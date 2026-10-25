<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cms\Category;
use App\Models\Cms\ContentModel;
use App\Services\Admin\CategoryService;
use App\Support\Locale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    /** 栏目列表 */
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => $this->categoryService->flattenTree(),
        ]);
    }

    /** 新建栏目 */
    public function create(Request $request): View
    {
        $category = new Category(['status' => 'published', 'type' => 'list', 'is_menu' => true, 'page_size' => 10]);
        $parent = null;
        $parentId = $request->integer('parent_id') ?: null;
        if ($parentId) {
            $parent = Category::query()->find($parentId);
            if ($parent) {
                $category->parent_id = $parent->id;
                $category->type = $parent->type;
                $category->model_id = $parent->model_id;
                $category->page_size = $parent->page_size ?: 10;
                $category->is_menu = false;
            }
        }

        return view('admin.categories.form', [
            'category' => $category,
            'parent' => $parent,
            'locales' => Locale::codesDefaultFirst(),
            'models' => ContentModel::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'parents' => $this->categoryService->parentsForSelect(),
        ]);
    }

    /** 保存栏目 */
    public function store(Request $request): RedirectResponse
    {
        $result = $this->categoryService->save(null, $this->saveInput($request));

        return redirect()->route('admin.categories.index')->with(
            $result['ok'] ? 'status' : 'error',
            $result['msg']
        );
    }

    /** 编辑栏目 */
    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parent' => $category->parent,
            'locales' => Locale::codesDefaultFirst(),
            'models' => ContentModel::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'parents' => $this->categoryService->parentsForSelect($category),
        ]);
    }

    /** 更新栏目 */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $result = $this->categoryService->save($category, $this->saveInput($request));

        return redirect()->route('admin.categories.index')->with(
            $result['ok'] ? 'status' : 'error',
            $result['msg']
        );
    }

    private function saveInput(Request $request): array
    {
        $request->validate(['purpose' => 'nullable|in:article,single,link,hub,custom']);
        $input = $request->all();
        $purpose = $request->input('purpose', 'custom');
        if (in_array($purpose, ['article', 'single', 'link', 'hub'], true)) {
            $input['type'] = $purpose === 'article' ? 'list' : $purpose;
            $input['model_id'] = null;
            if (in_array($purpose, ['article', 'single'], true)) {
                $model = ContentModel::query()->where('slug', $purpose === 'single' ? 'page' : 'article')->where('is_active', true)->first();
                if (! $model) {
                    throw ValidationException::withMessages(['purpose' => admin_text('该用途的模型未启用，请选择自定义内容')]);
                }
                $input['model_id'] = $model->id;
            }
            if ($purpose === 'link') {
                $request->validate(['url' => 'required|url:http,https']);
            }
        }

        return $input;
    }

    /** 删除栏目 */
    public function destroy(Category $category): RedirectResponse
    {
        $result = $this->categoryService->delete($category);

        return redirect()->route('admin.categories.index')->with(
            $result['ok'] ? 'status' : 'error',
            $result['msg']
        );
    }
}
