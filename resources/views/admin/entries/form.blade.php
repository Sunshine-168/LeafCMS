@extends('admin.layout')
@section('title', $entry->exists ? admin_text('编辑').' '.$model->displayName() : admin_text('写').' '.$model->displayName())
@section('fullwidth', true)
@php
    $canPublish = $canPublish ?? auth('admin')->user()?->canPerm('publish');
    $statusVal = old('status', $entry->status);
    $flagOpts = \App\Models\Cms\CmsFlag::displayOptions();
    $currentFlags = old('flag_list', array_filter(array_map('trim', explode(',', (string) old('flags', $entry->flags)))));
    $pubAt = old('published_at', optional($entry->published_at)->format('Y-m-d\TH:i'));
    if (! session()->hasOldInput('status') && $entry->exists && $entry->isScheduled()) {
        $statusVal = 'scheduled';
    }
    $extraCats = old('category_ids', $entry->relationLoaded('categories')
        ? $entry->categories->pluck('id')->all()
        : ($entry->exists ? $entry->categories()->pluck('cms_categories.id')->all() : []));
    $extraCats = array_map('intval', (array) $extraCats);
@endphp
@section('content')
    <div id="entry-recovery" class="alert alert-warning entry-recovery" hidden>
        <div class="entry-recovery-row">
            <p id="entry-recovery-message">{{ admin_text('有一份未保存的草稿。') }}</p>
            <button type="button" class="btn btn-sm" id="entry-recover">{{ admin_text('恢复草稿') }}</button>
            <button type="button" class="btn btn-muted btn-sm" id="entry-discard-recovery">{{ admin_text('丢弃草稿') }}</button>
        </div>
    </div>
    <form method="post" class="entry-form" id="entry-workflow"
          data-draft-key="cms-entry:{{ auth('admin')->id() }}:{{ \App\Support\CurrentSite::idOrDefault() }}:{{ $model->id }}:{{ $entry->id ?? 'new' }}"
          data-new-draft-key="cms-entry:{{ auth('admin')->id() }}:{{ \App\Support\CurrentSite::idOrDefault() }}:{{ $model->id }}:new"
          data-saved="{{ session('entry_saved') ? '1' : '0' }}"
          data-created="{{ session('entry_created') ? '1' : '0' }}"
          data-saved-draft-id="{{ session('entry_saved_draft_id') }}"
          data-recovered-draft-id="{{ session('entry_recovered_draft_id') }}"
          data-preview-url="{{ session('entry_preview_url') }}"
          data-updated-at="{{ $entry->updated_at?->toIso8601String() }}"
          data-was-published="{{ $entry->status === 'published' ? '1' : '0' }}"
          action="{{ $entry->exists ? route('admin.entries.update', $entry) : route('admin.entries.store') }}">
        @csrf
        @if($entry->exists) @method('PUT') @endif
        <input type="hidden" name="model_id" value="{{ $model->id }}">
        <input type="hidden" name="draft_id" id="entry-draft-id">
        <input type="hidden" name="recovered_draft_id" id="entry-recovered-draft-id">
        @if($entry->exists)
            <input type="hidden" name="edit_version" value="{{ old('edit_version', app(\App\Services\Admin\EntryService::class)->editVersion($entry)) }}">
        @endif

        <div class="entry-layout">
            <div class="entry-main">
                <div class="card card-panel">
                    <div class="card-header">
                        <span>{{ $entry->exists ? admin_text('编辑内容') : admin_text('写内容') }} · {{ $model->displayName() }}</span>
                    </div>
                    <div class="card-body">
                        @include('admin.partials.locale-tabs', [
                            'locales' => $locales,
                            'model' => $entry,
                            'hideCode' => true,
                            'fields' => [
                                'title' => ['label' => admin_text('标题'), 'class' => 'entry-title', 'placeholder' => admin_text('读者看到的标题')],
                                'slug' => ['label' => admin_text('网址标识'), 'hint' => admin_text('出现在链接里。可留空，保存时按标题生成。'), 'placeholder' => 'about-us'],
                                'excerpt' => ['label' => admin_text('摘要'), 'type' => 'textarea', 'rows' => 3],
                                'content' => ['label' => admin_text('正文'), 'type' => 'editor'],
                            ],
                            'advanced' => [
                                'meta_title' => ['label' => admin_text('搜索标题')],
                                'meta_description' => ['label' => admin_text('搜索描述'), 'type' => 'textarea'],
                            ],
                        ])

                        @if($model->fields->isNotEmpty())
                            <details class="entry-more" open>
                                <summary>{{ admin_text('更多字段') }} · {{ $model->displayName() }}</summary>
                                <p class="muted" id="fieldBindHint">{{ admin_text('选好主栏目后，只显示该栏目用到的字段。') }}</p>
                                @foreach($model->fields as $field)
                                    <div class="cms-field-bind" data-bound-cats="{{ $field->boundCatsAttr() }}">
                                    <label>{{ $field->displayLabel() }}</label>
                                    @php $val = old('attrs.'.$field->name, $entry->attr($field->name)); @endphp
                                    @if($field->type === 'editor')
                                        <textarea class="cms-editor" name="attrs[{{ $field->name }}]">{{ $val }}</textarea>
                                    @elseif($field->type === 'textarea')
                                        <textarea name="attrs[{{ $field->name }}]" rows="4">{{ $val }}</textarea>
                                    @elseif($field->type === 'images')
                                        <div class="images-gallery" data-images-input="#attr_{{ $field->name }}">
                                            <div class="cover-row">
                                                <input type="text" id="attr_{{ $field->name }}" name="attrs[{{ $field->name }}]" value="{{ is_array($val) ? implode(',', $val) : $val }}" placeholder="{{ admin_text('多图 URL，逗号分隔') }}">
                                                <button type="button" class="btn btn-muted" data-media-pick-append="#attr_{{ $field->name }}">{{ admin_text('添加图片') }}</button>
                                            </div>
                                            <div class="images-thumbs"></div>
                                        </div>
                                    @elseif(in_array($field->type, ['image', 'file'], true))
                                        @php $fileVal = is_array($val) ? implode(',', $val) : $val; @endphp
                                        <div class="media-field" @if($field->type === 'image') data-media-preview="#attr_{{ $field->name }}" @endif>
                                            @if($field->type === 'image')
                                                <div class="media-preview" @if(! $fileVal) hidden @endif>
                                                    <img src="{{ $fileVal }}" alt="{{ $field->displayLabel() }} · {{ admin_text('预览') }}">
                                                    <button type="button" class="media-preview-clear" title="{{ admin_text('移除') }}">&times;</button>
                                                </div>
                                            @endif
                                            <div class="cover-row">
                                                <input type="text" id="attr_{{ $field->name }}" name="attrs[{{ $field->name }}]" value="{{ $fileVal }}">
                                                <button type="button" class="btn btn-muted" data-media-pick="#attr_{{ $field->name }}">{{ admin_text('选择媒体') }}</button>
                                            </div>
                                        </div>
                                    @elseif($field->relatedModelSlug())
                                        @php
                                            $relatedSlug = $field->relatedModelSlug();
                                            $relatedModelId = \App\Models\Cms\ContentModel::query()->where('slug', $relatedSlug)->value('id');
                                            $relatedEntries = $relatedModelId
                                                ? \App\Models\Cms\Entry::query()->published()->where('model_id', $relatedModelId)->orderByDesc('id')->limit(300)->get()
                                                : collect();
                                        @endphp
                                        <select name="attrs[{{ $field->name }}]">
                                            <option value="">— {{ admin_text('选择') }} · {{ $field->displayLabel() }} —</option>
                                            @foreach($relatedEntries as $rel)
                                                <option value="{{ $rel->id }}" @selected((string)$val === (string)$rel->id)>{{ $rel->t('title') }} (#{{ $rel->id }})</option>
                                            @endforeach
                                        </select>
                                    @elseif($field->type === 'number')
                                        <input type="number" step="any" name="attrs[{{ $field->name }}]" value="{{ $val }}">
                                    @elseif($field->type === 'url')
                                        <input type="url" name="attrs[{{ $field->name }}]" value="{{ $val }}" placeholder="https://">
                                    @elseif($field->type === 'date')
                                        <input type="date" name="attrs[{{ $field->name }}]" value="{{ $val }}">
                                    @elseif($field->type === 'datetime')
                                        <input type="datetime-local" name="attrs[{{ $field->name }}]" value="{{ $val }}">
                                    @elseif($field->type === 'checkbox' || $field->type === 'switch')
                                        <label class="inline"><input type="checkbox" name="attrs[{{ $field->name }}]" value="1" @checked((string)$val === '1' || $val === true || $val === 'true')> {{ admin_text('是') }}</label>
                                    @elseif($field->type === 'radio')
                                        @foreach(($field->options['choices'] ?? []) as $k => $label)
                                            @php $opt = is_int($k) ? $label : $k; @endphp
                                            <label class="inline" style="margin-right:12px">
                                                <input type="radio" name="attrs[{{ $field->name }}]" value="{{ $opt }}" @checked((string)$val === (string)$opt)> {{ $label }}
                                            </label>
                                        @endforeach
                                    @elseif($field->type === 'select')
                                        <select name="attrs[{{ $field->name }}]">
                                            <option value="">—</option>
                                            @foreach(($field->options['choices'] ?? []) as $k => $label)
                                                @php $opt = is_int($k) ? $label : $k; @endphp
                                                <option value="{{ $opt }}" @selected($val == $opt)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($field->type === 'map')
                                        @include('admin.partials.map-picker', [
                                            'name' => 'attrs['.$field->name.']',
                                            'value' => $val,
                                            'defaults' => $mapDefaults ?? ['lng' => 116.397, 'lat' => 39.908],
                                        ])
                                    @else
                                        <input type="text" name="attrs[{{ $field->name }}]" value="{{ $val }}">
                                    @endif
                                    </div>
                                @endforeach
                            </details>
                        @endif
                    </div>
                </div>
                @if($entry->exists)
                    @php $revisions = $revisions ?? $entry->revisions ?? collect(); @endphp
                    <div class="card card-panel">
                        <div class="card-header"><span>{{ admin_text('草稿预览') }}</span></div>
                        <div class="card-body">
                            <p class="muted">{{ admin_text('未发布内容可用此链接查看。缓存和统计会跳过预览页。') }}</p>
                            @if($entry->preview_token)
                                <p>
                                    <a href="{{ $entry->previewUrl() }}" target="_blank" rel="noopener">{{ admin_text('打开预览') }}</a>
                                    @if($entry->preview_expires_at)
                                        <span class="muted">{{ admin_text('有效至') }} {{ $entry->preview_expires_at->format('Y-m-d H:i') }}</span>
                                    @endif
                                </p>
                            @else
                                <p class="muted">{{ admin_text('保存后会生成预览链接。') }}</p>
                            @endif
                            <button class="btn btn-sm btn-muted" type="submit" form="entry-preview-refresh">{{ admin_text('刷新预览链接') }}</button>
                        </div>
                    </div>
                    <div class="card card-panel">
                        <div class="card-header"><span>{{ admin_text('历史版本') }}</span></div>
                        <div class="card-body">
                            <p class="muted">{{ admin_text('每次保存会留下上一稿，最多 20 条。') }}</p>
                            @forelse($revisions as $rev)
                                <div class="todo-row" style="padding-left:0;padding-right:0">
                                    <div class="grow">
                                        <strong>{{ $rev->title_hint ?: admin_text('（无标题）') }}</strong>
                                        <div class="muted">{{ $rev->created_at?->format('Y-m-d H:i') }} · {{ $rev->admin?->name ?: admin_text('系统') }}</div>
                                    </div>
                                    <button class="btn btn-sm btn-muted" type="submit" form="entry-revision-{{ $rev->id }}">{{ admin_text('还原') }}</button>
                                </div>
                            @empty
                                <p class="muted" style="margin:0">{{ admin_text('尚无历史版本，再次保存后出现。') }}</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            <aside class="entry-aside">
                <div class="card card-panel">
                    <div class="card-header"><span>{{ admin_text('发布') }}</span></div>
                    <div class="card-body">
                        <label for="entry-status">{{ admin_text('保存方式') }}</label>
                        <select id="entry-status" name="status">
                            @if($canPublish)
                                <option value="draft" @selected($statusVal === 'draft')>{{ admin_text('草稿') }}</option>
                                <option value="pending" @selected($statusVal === 'pending')>{{ admin_text('待审') }}</option>
                                <option value="published" @selected($statusVal === 'published')>{{ admin_text('立即发布 / 更新线上内容') }}</option>
                                <option value="scheduled" @selected($statusVal === 'scheduled')>{{ admin_text('定时发布') }}</option>
                            @elseif($entry->status === 'published')
                                <option value="published" selected>{{ admin_text('已发布') }}</option>
                            @else
                                <option value="draft" @selected($statusVal === 'draft')>{{ admin_text('草稿') }}</option>
                                <option value="pending" @selected($statusVal === 'pending')>{{ admin_text('提交待审') }}</option>
                            @endif
                        </select>
                        @if(! $canPublish)
                            <p class="muted">{{ admin_text('没有发布权限，可保存草稿或提交审核。') }}</p>
                        @endif
                        <label for="entry-published-at">{{ admin_text('定时发布') }}</label>
                        <input id="entry-published-at" type="datetime-local" name="published_at" value="{{ $pubAt }}">
                        <p class="muted field-hint">{{ admin_text('schedule_timezone', ['timezone' => config('app.timezone')]) }}</p>
                        <p class="muted" id="entry-publish-summary" role="status">{{ admin_text('按所选保存方式生效；预览前也会先保存。') }}</p>
                        <label class="inline"><input type="checkbox" name="is_homepage" value="1" @checked(old('is_homepage', $entry->is_homepage))> {{ admin_text('设为首页文档') }}</label>
                        <div class="entry-save">
                            <button class="btn" type="submit" name="submit_action" value="save" id="entry-save-action">{{ ['draft' => admin_text('保存草稿'), 'pending' => admin_text('提交审核'), 'published' => ($entry->exists && $entry->status === 'published' ? admin_text('保存修改稿') : admin_text('更新已发布内容')), 'scheduled' => admin_text('保存定时发布')][$statusVal] ?? admin_text('保存草稿') }}</button>
                            @if($canPublish && $entry->exists && $entry->status === 'published')
                                <button class="btn" type="submit" name="submit_action" value="publish">{{ admin_text('发布到线上') }}</button>
                            @endif
                            <button class="btn btn-muted" type="submit" name="submit_action" value="preview">{{ admin_text('保存并预览') }}</button>
                            <a class="btn btn-muted" href="{{ route('admin.entries.index', ['model_id' => $model->id]) }}">{{ admin_back('entries') }}</a>
                        </div>
                        <label for="entry-preview-locale">{{ admin_text('预览语言') }}</label>
                        <select name="preview_locale" id="entry-preview-locale">
                            @foreach($locales as $code)
                                <option value="{{ $code }}">{{ \App\Support\Locale::configured()[$code]['label'] ?? $code }}</option>
                            @endforeach
                        </select>
                        <p class="muted field-hint" id="entry-save-state" role="status">{{ admin_text('尚无未保存修改。本地副本仅保存在当前浏览器，最长保留 7 天。') }}</p>
                    </div>
                </div>

                <div class="card card-panel">
                    <div class="card-header"><span>{{ admin_text('栏目与展示') }}</span></div>
                    <div class="card-body">
                        <label for="entryPrimaryCategory">{{ admin_text('主栏目') }}</label>
                        <select name="category_id" id="entryPrimaryCategory">
                            <option value="">{{ admin_text('选择栏目') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $entry->category_id) == $category->id)>{{ $category->t('title') }}</option>
                            @endforeach
                        </select>
                        <p class="muted field-hint">{{ admin_text('决定这篇内容的网址。') }}</p>
                        @if($categories->count() > 1)
                            <label>{{ admin_text('同时出现在') }}</label>
                            <div class="choice-grid">
                                @foreach($categories as $category)
                                    <label class="inline">
                                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}"
                                            @checked(in_array((int)$category->id, $extraCats, true))>
                                        {{ $category->t('title') }}
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <label>{{ admin_text('封面') }}</label>
                        @php $coverVal = old('cover', $entry->cover); @endphp
                        <div class="media-field" data-media-preview="#entryCover">
                            <div class="media-preview" @if(! $coverVal) hidden @endif>
                                <img src="{{ $coverVal }}" alt="{{ admin_text('封面预览') }}">
                                <button type="button" class="media-preview-clear" title="{{ admin_text('移除封面') }}">&times;</button>
                            </div>
                            <div class="cover-row">
                                <input type="text" id="entryCover" name="cover" value="{{ $coverVal }}" placeholder="{{ admin_text('图片地址') }}">
                                <button type="button" class="btn btn-muted" data-media-pick="#entryCover">{{ admin_text('选择') }}</button>
                            </div>
                        </div>

                        @if($tags->isNotEmpty())
                            <label>{{ admin_text('标签') }}</label>
                            <div class="choice-grid">
                                @foreach($tags as $tag)
                                    <label class="inline">
                                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                                            @checked(collect(old('tag_ids', $entry->tags?->pluck('id')->all() ?? []))->contains($tag->id))>
                                        {{ $tag->t('title') }}
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <details class="card card-panel entry-aside-more">
                    <summary>{{ admin_text('更多设置') }}</summary>
                    <div class="card-body">
                        <label>{{ admin_text('推荐属性') }}</label>
                        <div class="choice-grid">
                            @forelse($flagOpts as $key => $label)
                                <label class="inline">
                                    <input type="checkbox" name="flag_list[]" value="{{ $key }}" @checked(in_array($key, $currentFlags, true))>
                                    {{ $label }}
                                </label>
                            @empty
                                <span class="muted">{{ admin_text('还没有推荐属性') }}</span>
                            @endforelse
                        </div>
                        <label>{{ admin_text('其它标记') }}</label>
                        <input type="text" name="flags" value="{{ old('flags', collect($currentFlags)->diff(array_keys($flagOpts))->implode(',')) }}" placeholder="{{ admin_text('逗号分隔，可空') }}">
                        <label>{{ admin_text('排序') }}</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $entry->sort_order ?? 0) }}">
                    </div>
                </details>
            </aside>
        </div>
    </form>

    @if($entry->exists)
        <form id="entry-preview-refresh" method="post" action="{{ route('admin.entries.preview', $entry) }}" hidden>
            @csrf
        </form>
        @foreach($revisions ?? $entry->revisions ?? collect() as $rev)
            <form id="entry-revision-{{ $rev->id }}" method="post" action="{{ route('admin.entries.revisions.restore', [$entry, $rev]) }}" hidden onsubmit="return confirm(@js(admin_text('restore_revision_confirm')))">
                @csrf
            </form>
        @endforeach
    @endif
    @php $workflowMessages = collect(array (
  0 => 'restore_prompt',
  1 => 'restore_confirm',
  2 => 'restore_revision_confirm',
  3 => 'demo_confirm',
  4 => 'site_context',
  5 => 'theme_context',
  6 => 'schedule_timezone',
  7 => 'preview_saved_at',
  8 => 'parent_context',
  9 => 'entry_title',
  10 => 'scheduled_message',
  11 => 'local_clean',
  12 => 'local_saved',
  13 => 'local_failed',
  14 => 'server_saved',
  15 => 'drafts_found',
  16 => 'server_changed',
  17 => 'recover_reminder',
  18 => 'draft_missing',
  19 => 'draft_unreadable',
  20 => 'local_unavailable',
  21 => 'untitled',
  22 => 'draft_summary',
  23 => 'pending_summary',
  24 => 'schedule_summary',
  25 => 'published_summary',
  26 => 'preview_summary',
  27 => '保存草稿',
  28 => '提交待审',
  29 => '提交审核',
  30 => '保存定时发布',
  31 => '更新已发布内容',
  32 => '立即发布',
  33 => '保存修改稿',
  34 => '提交修改稿审核',
  35 => '保存修改稿不会替换线上内容。预览和送审也只针对修改稿，点「发布到线上」才替换。',
))->mapWithKeys(fn ($key) => [$key => admin_text($key)])->all(); @endphp
    <script id="entry-workflow-messages" type="application/json">@json($workflowMessages)</script>
    @include('admin.partials.editor-assets')
    <script src="{{ asset('assets/entry-workflow.js') }}?v={{ filemtime(public_path('assets/entry-workflow.js')) }}" defer></script>
    <script>
    (function () {
        const sel = document.getElementById('entryPrimaryCategory');
        const blocks = document.querySelectorAll('.cms-field-bind');
        if (!sel || !blocks.length) return;
        const apply = () => {
            const cat = String(sel.value || '');
            blocks.forEach((el) => {
                const bound = el.getAttribute('data-bound-cats') || '*';
                const show = bound === '*' || (cat !== '' && bound.split(',').includes(cat));
                el.style.display = show ? '' : 'none';
                el.querySelectorAll('input,select,textarea,button').forEach((inp) => {
                    if (show) inp.removeAttribute('disabled');
                    else inp.setAttribute('disabled', 'disabled');
                });
            });
        };
        sel.addEventListener('change', apply);
        apply();
    })();
    </script>
@endsection
