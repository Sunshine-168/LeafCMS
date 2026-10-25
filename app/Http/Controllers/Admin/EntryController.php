<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cms\CmsFlag;
use App\Models\Cms\ContentModel;
use App\Models\Cms\Entry;
use App\Models\Cms\EntryRevision;
use App\Models\Cms\Recommend;
use App\Models\Cms\Setting;
use App\Models\Cms\Tag;
use App\Services\Admin\CategoryService;
use App\Services\Admin\EntryService;
use App\Support\Locale;
use App\Support\TranslationWatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EntryController extends Controller
{
    public function __construct(
        private readonly EntryService $entryService,
        private readonly CategoryService $categoryService,
    ) {}

    /** 内容列表 */
    public function index(Request $request): View
    {
        $modelId = $request->integer('model_id') ?: null;
        $status = is_string($request->query('status')) ? $request->query('status') : null;
        $keyword = trim((string) $request->query('q', ''));
        $categoryId = $request->integer('category_id') ?: null;
        $tagId = $request->integer('tag_id') ?: null;
        $flag = trim((string) $request->query('flag', ''));
        $translation = trim((string) $request->query('translation', ''));
        if ($translation !== '') {
            TranslationWatch::backfillMissing();
        }
        $models = ContentModel::query()->where('is_active', true)->orderBy('sort_order')->get();
        $filterTag = $tagId ? Tag::query()->find($tagId) : null;
        $filterFlag = $flag !== '' ? CmsFlag::query()->where('name', $flag)->first() : null;

        return view('admin.entries.index', [
            'entries' => $this->entryService->paginate($modelId, $status, 15, $keyword, $categoryId, $tagId, $flag ?: null, $translation !== '' ? $translation : null),
            'models' => $models,
            'modelId' => $modelId,
            'currentModel' => $models->firstWhere('id', $modelId),
            'status' => $status,
            'q' => $keyword,
            'categoryId' => $categoryId,
            'tagId' => $tagId,
            'flag' => $flag,
            'translation' => $translation,
            'filterTag' => $filterTag,
            'filterFlag' => $filterFlag,
            'pendingCount' => $this->entryService->pendingCount($modelId),
            'categories' => $this->scopedCategories($modelId),
            'recommends' => Recommend::query()->where('is_active', true)->orderBy('id')->get(),
            'flagOptions' => CmsFlag::displayOptions(),
            'canPublish' => $this->entryService->canPublish(),
        ]);
    }

    protected function scopedCategories(?int $modelId = null)
    {
        $rows = $this->categoryService->flattenTree();
        if ($modelId) {
            $rows = $rows->filter(fn ($c) => (int) $c->model_id === (int) $modelId)->values();
        }

        return $rows;
    }

    /** 新建内容 */
    public function create(Request $request): View
    {
        $model = ContentModel::query()->findOrFail($request->integer('model_id') ?: ContentModel::query()->value('id'));

        return view('admin.entries.form', [
            'entry' => new Entry(['status' => 'draft', 'model_id' => $model->id, 'attrs' => []]),
            'model' => $model->load(['fields.categories']),
            'categories' => $this->scopedCategories($model->id),
            'tags' => Tag::query()->orderByDesc('id')->get(),
            'locales' => Locale::codesDefaultFirst(),
            'canPublish' => $this->entryService->canPublish(),
            'mapDefaults' => [
                'lng' => (float) Setting::getValue('map_default_lng', '116.397'),
                'lat' => (float) Setting::getValue('map_default_lat', '39.908'),
            ],
        ]);
    }

    /** 保存新建 */
    public function store(Request $request): RedirectResponse
    {
        $result = $this->entryService->save(null, $this->saveInput($request));
        if ($result['ok'] && $result['data'] instanceof Entry) {
            return $this->savedResponse($request, $result['data'], true, $result['msg']);
        }

        return back()->withInput()->with('error', $result['msg']);
    }

    /** 编辑内容 */
    public function edit(Entry $entry): View
    {
        $entry->load(['tags', 'categories', 'revisions.admin']);
        $entry->overlayWorkingDraft();
        $model = $entry->modelDef()->with(['fields.categories'])->firstOrFail();

        return view('admin.entries.form', [
            'entry' => $entry,
            'model' => $model,
            'categories' => $this->scopedCategories($model->id),
            'tags' => Tag::query()->orderByDesc('id')->get(),
            'locales' => Locale::codesDefaultFirst(),
            'revisions' => $entry->revisions,
            'canPublish' => $this->entryService->canPublish(),
            'mapDefaults' => [
                'lng' => (float) Setting::getValue('map_default_lng', '116.397'),
                'lat' => (float) Setting::getValue('map_default_lat', '39.908'),
            ],
        ]);
    }

    /** 更新内容 */
    public function update(Request $request, Entry $entry): RedirectResponse
    {
        $result = $this->entryService->save($entry, $this->saveInput($request));

        if ($result['ok']) {
            return $this->savedResponse($request, $result['data'], false, $result['msg']);
        }

        return redirect()->route('admin.entries.edit', $entry)
            ->withInput()->with('error', $result['msg']);
    }

    private function saveInput(Request $request): array
    {
        $request->validate([
            'submit_action' => 'nullable|in:save,draft,preview,publish',
            'status' => 'nullable|in:draft,pending,published,scheduled',
            'published_at' => 'nullable|date',
            'edit_version' => 'nullable|string|size:64',
            'draft_id' => 'nullable|regex:/^[a-zA-Z0-9_-]{1,80}$/',
            'recovered_draft_id' => 'nullable|regex:/^[a-zA-Z0-9_-]{1,80}$/',
            'preview_locale' => ['nullable', Rule::in(Locale::codes())],
        ]);
        $input = $request->all();
        if ($request->input('submit_action') === 'draft') {
            $input['status'] = 'draft';
            $input['published_at'] = null;
        } elseif ($request->input('status') === 'scheduled') {
            $request->validate(['published_at' => 'required|date|after:now']);
            $input['status'] = 'published';
        } elseif (in_array($request->input('submit_action'), ['save', 'preview', 'publish'], true) && $request->input('status') === 'published') {
            // “立即发布”不沿用旧的定时日期。
            $input['published_at'] = now();
        }

        return $input;
    }

    private function savedResponse(Request $request, Entry $entry, bool $created = false, ?string $message = null): RedirectResponse
    {
        $message = ($message && $message !== '保存成功') ? $message : match ($entry->status) {
            'draft' => admin_text('草稿已保存，前台不可见'),
            'pending' => admin_text('已提交审核，审核发布后前台可见'),
            default => $entry->isScheduled()
                ? admin_text('scheduled_message', ['time' => $entry->published_at->format('Y-m-d H:i'), 'timezone' => config('app.timezone')])
                : admin_text('内容已发布，前台可见'),
        };

        return redirect()->route('admin.entries.edit', $entry)
            ->with('status', $message)
            ->with('entry_saved', true)
            ->with('entry_created', $created)
            ->with('entry_saved_draft_id', $request->input('draft_id'))
            ->with('entry_recovered_draft_id', $request->input('recovered_draft_id'))
            ->with('entry_preview_url', $request->input('submit_action') === 'preview'
                ? route('admin.entries.preview.show', ['entry' => $entry, 'locale' => $request->input('preview_locale', Locale::default())]) : null);
    }

    public function preview(Entry $entry, Request $request): View|RedirectResponse
    {
        abort_if($this->entryService->assertAccess($entry), 403);
        $locale = $request->query('locale', Locale::default());
        abort_unless(is_string($locale) && in_array($locale, Locale::codes(), true), 404);
        if (! $entry->previewIsValid()) {
            return redirect()->route('admin.entries.edit', $entry)->with('error', admin_text('预览链接已失效，请保存内容后重新预览'));
        }
        $url = $entry->previewUrl();
        if ($locale !== Locale::default() || ! Locale::hideDefault()) {
            $url = '/'.Locale::toPublic($locale).'/preview/'.$entry->preview_token;
        }

        return view('admin.entries.preview', compact('entry', 'locale', 'url'));
    }

    /** 刷新草稿预览链接 */
    public function issuePreview(Entry $entry): RedirectResponse
    {
        $result = $this->entryService->issuePreviewToken($entry);

        return redirect()->route('admin.entries.edit', $entry)
            ->with($result['ok'] ? 'status' : 'error', $result['msg']);
    }

    /** 还原历史版本 */
    public function restoreRevision(Entry $entry, EntryRevision $revision): RedirectResponse
    {
        $result = $this->entryService->restoreRevision($entry, $revision);

        return redirect()->route('admin.entries.edit', $entry)
            ->with($result['ok'] ? 'status' : 'error', $result['msg']);
    }

    /** 删除内容（进回收站） */
    public function destroy(Entry $entry): RedirectResponse
    {
        $modelId = $entry->model_id;
        $result = $this->entryService->delete($entry);

        return redirect()->route('admin.entries.index', ['model_id' => $modelId])
            ->with('status', $result['msg']);
    }

    /** 批量操作 */
    public function batch(Request $request): RedirectResponse
    {
        $ids = array_values(array_filter(array_map('intval', (array) $request->input('ids', []))));
        $action = (string) $request->input('action');
        $result = match ($action) {
            'publish' => $this->entryService->batchStatus($ids, 'published'),
            'pending' => $this->entryService->batchStatus($ids, 'pending'),
            'draft' => $this->entryService->batchStatus($ids, 'draft'),
            'delete' => $this->entryService->batchDelete($ids),
            'move' => $this->entryService->batchMove($ids, (int) $request->input('category_id')),
            'copy' => $this->entryService->batchCopy($ids, $request->filled('category_id') ? (int) $request->input('category_id') : null),
            'push' => $this->entryService->batchPush($ids, (int) $request->input('recommend_id')),
            default => ['ok' => false, 'msg' => admin_text('未知操作')],
        };

        return redirect()->route('admin.entries.index', $this->listQuery($request))
            ->with($result['ok'] ? 'status' : 'error', $result['msg']);
    }

    /** @return array<string, int|string> */
    protected function listQuery(Request $request): array
    {
        return array_filter([
            'model_id' => $request->input('model_id'),
            'status' => $request->input('keep_status'),
            'q' => $request->input('keep_q'),
            'category_id' => $request->input('keep_category_id'),
            'tag_id' => $request->input('keep_tag_id'),
            'flag' => $request->input('keep_flag'),
        ], fn ($v) => $v !== null && $v !== '');
    }
}
