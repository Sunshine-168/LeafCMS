@extends('admin.layout')
@section('title', $category->exists ? admin_text('编辑栏目') : ($parent ? admin_text('新建下级栏目') : admin_text('新建栏目')))
@section('icon', 'sitemap')
@section('content')
    @if($parent)
        <p class="muted recycle-lead">{{ admin_text('parent_context', ['name' => $parent->t('title')]) }}</p>
    @endif
    <form method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if($category->exists) @method('PUT') @endif

        @include('admin.partials.locale-tabs', [
            'locales' => $locales,
            'model' => $category,
            'hideCode' => true,
            'fields' => [
                'title' => ['label' => admin_text('名称'), 'placeholder' => admin_text('如 公司新闻')],
                'slug' => ['label' => admin_text('网址标识'), 'placeholder' => admin_text('出现在栏目网址里，可空'), 'hint' => admin_text('不要用 search、sitemap、member 这些系统路径')],
            ],
            'advanced' => [
                'meta_title' => ['label' => admin_text('SEO 标题')],
                'meta_description' => ['label' => admin_text('SEO 描述'), 'type' => 'textarea'],
            ],
        ])

        <label for="cat-parent">{{ admin_text('上级栏目') }}</label>
        <select name="parent_id" id="cat-parent">
            <option value="">{{ admin_text('顶级栏目（不挂在任何栏目下）') }}</option>
            @foreach($parents ?? [] as $item)
                <option value="{{ $item->id }}" @selected((int) old('parent_id', $category->parent_id) === (int) $item->id)>
                    {{ str_repeat('— ', (int) ($item->depth ?? 0)) }}{{ $item->t('title') }}
                </option>
            @endforeach
        </select>
        <p class="muted field-hint">{{ admin_text('选上级即可做多级栏目，例如 新闻中心 → 公司新闻。') }}</p>

        <label for="category-purpose">{{ admin_text('我要创建') }}</label>
        <select name="purpose" id="category-purpose">
            @foreach(['article' => admin_text('文章列表（如公司新闻）'), 'single' => admin_text('独立页面（如关于我们）'), 'link' => admin_text('外部链接（跳转到其他网站）'), 'hub' => admin_text('栏目目录（组织下级栏目）'), 'custom' => admin_text('自定义内容（产品、下载等）')] as $value => $label)
                <option value="{{ $value }}" @selected(old('purpose', $category->exists || $category->model_id ? 'custom' : 'article') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="muted field-hint">{{ admin_text('常用用途会自动配好类型和模型；产品、下载等内容可选自定义。') }}</p>
        <div id="category-external-url">
            <label for="category-url">{{ admin_text('外链 URL（类型为外链时填写）') }}</label>
            <input id="category-url" type="url" name="url" value="{{ old('url', $category->getAttributes()['url'] ?? '') }}" placeholder="https://example.com">
        </div>
        <details class="entry-seo" id="category-advanced" @if($category->exists) open @endif>
        <summary>{{ admin_text('高级类型与模型') }}</summary>
        <label>{{ admin_text('类型') }}</label>
        <select name="type">
            @foreach(['hub'=>admin_text('频道（只做目录，下面再挂列表）'),'list'=>admin_text('列表'),'single'=>admin_text('单页'),'link'=>admin_text('外链')] as $k=>$label)
                <option value="{{ $k }}" @selected(old('type', $category->type ?? 'list') === $k)>{{ $label }}</option>
            @endforeach
        </select>

        <label>{{ admin_text('绑定模型') }}</label>
        <select name="model_id">
            <option value="">—</option>
            @foreach($models as $model)
                <option value="{{ $model->id }}" data-slug="{{ $model->slug }}" @selected(old('model_id', $category->model_id) == $model->id)>{{ $model->displayName() }}</option>
            @endforeach
        </select>
        </details>

        <details class="entry-seo">
            <summary>{{ admin_text('更多设置') }}</summary>
            <label>{{ admin_text('列表模板（如 list.list_product）') }}</label>
            <input type="text" name="list_tpl" value="{{ old('list_tpl', $category->list_tpl) }}">
            <label>{{ admin_text('详情模板') }}</label>
            <input type="text" name="show_tpl" value="{{ old('show_tpl', $category->show_tpl) }}">
            <label>{{ admin_text('频道模板') }}</label>
            <input type="text" name="category_tpl" value="{{ old('category_tpl', $category->category_tpl) }}">
            <label>{{ admin_text('分页条数') }}</label>
            <input type="number" name="page_size" value="{{ old('page_size', $category->page_size ?? 10) }}">
            <label>{{ admin_text('同级排序') }}</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
            <p class="muted field-hint">{{ admin_text('数字越小越靠前。只在同一上级下比较。') }}</p>
        </details>
        <label class="inline"><input type="checkbox" name="is_menu" value="1" @checked(old('is_menu', $category->is_menu ?? true))> {{ admin_text('显示在导航') }}</label>
        <label>{{ admin_text('状态') }}</label>
        <select name="status">
            <option value="published" @selected(old('status', $category->status) === 'published')>{{ admin_text('已发布') }}</option>
            <option value="draft" @selected(old('status', $category->status) === 'draft')>{{ admin_text('草稿') }}</option>
        </select>

        <p style="margin-top:16px;">
            <button class="btn" type="submit">{{ admin_text('保存') }}</button>
            <a class="btn btn-muted" href="{{ route('admin.categories.index') }}">{{ admin_back('categories') }}</a>
        </p>
    </form>
    <script>
    (function () {
        var purpose = document.getElementById('category-purpose');
        var form = purpose.form;
        var type = form.elements.type;
        var model = form.elements.model_id;
        function urlVisibility() {
            document.getElementById('category-external-url').hidden = type.value !== 'link';
            document.getElementById('category-url').required = type.value === 'link';
        }
        function applyPurpose() {
            var value = purpose.value;
            if (value === 'custom') {
                document.getElementById('category-advanced').open = true;
            } else {
                type.value = value === 'article' ? 'list' : value;
                var slug = value === 'article' ? 'article' : value === 'single' ? 'page' : '';
                var option = Array.from(model.options).find(function (item) { return item.dataset.slug === slug; });
                model.value = option ? option.value : '';
            }
            urlVisibility();
        }
        purpose.addEventListener('change', applyPurpose);
        type.addEventListener('change', function () { purpose.value = 'custom'; urlVisibility(); });
        model.addEventListener('change', function () { purpose.value = 'custom'; });
        applyPurpose();
    })();
    </script>
@endsection
