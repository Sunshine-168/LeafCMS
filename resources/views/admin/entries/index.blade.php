@extends('admin.layout')
@section('title', admin_t('nav.entries'))
@section('icon', 'file-alt')
@php
    $status = $status ?? '';
    $q = $q ?? '';
    $categoryId = $categoryId ?? null;
    $tagId = $tagId ?? null;
    $flag = $flag ?? '';
    $translation = $translation ?? '';
    $flagOptions = $flagOptions ?? [];
    $canPublish = $canPublish ?? false;
    $qs = function (array $extra = []) use ($modelId, $status, $q, $categoryId, $tagId, $flag, $translation) {
        return route('admin.entries.index', array_filter(
            array_merge([
                'model_id' => $modelId,
                'status' => $status,
                'q' => $q,
                'category_id' => $categoryId,
                'tag_id' => $tagId,
                'flag' => $flag,
                'translation' => $translation,
            ], $extra),
            fn ($v) => $v !== '' && $v !== null
        ));
    };
    $fmtTime = function ($dt) {
        if (! $dt) {
            return '—';
        }
        if ($dt->isToday()) {
            return admin_text('今天 ').$dt->format('H:i');
        }
        if ($dt->isYesterday()) {
            return admin_text('昨天 ').$dt->format('H:i');
        }

        return $dt->year === now()->year ? $dt->format('m-d H:i') : $dt->format('Y-m-d');
    };
    $createLabel = $currentModel?->name ? admin_text('新建').' '.$currentModel->displayName() : admin_text('新建');
    $hasFilters = $status !== '' || $q !== '' || $categoryId || $tagId || $flag !== '' || $translation !== '';
    $catLabel = function ($c) {
        $depth = (int) ($c->depth ?? 0);

        return ($depth > 0 ? str_repeat('—', $depth).' ' : '').($c->displayTitle() ?: admin_text('无标题'));
    };
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.recycle.index') }}"><i class="fas fa-trash-alt"></i>{{ admin_text('回收站') }}</a>
    @if($currentModel)
        <a class="btn btn-sm" href="{{ route('admin.entries.create', ['model_id' => $currentModel->id]) }}"><i class="fas fa-plus"></i> {{ $createLabel }}</a>
    @elseif($models->count() === 1)
        <a class="btn btn-sm" href="{{ route('admin.entries.create', ['model_id' => $models->first()->id]) }}"><i class="fas fa-plus"></i> {{ admin_text('新建') }} {{ $models->first()->displayName() }}</a>
    @elseif($models->isNotEmpty())
        <details class="create-menu">
            <summary class="btn btn-sm"><i class="fas fa-plus"></i>{{ admin_text('新建') }}</summary>
            <div class="create-menu-list">
                @foreach($models as $m)
                    <a href="{{ route('admin.entries.create', ['model_id' => $m->id]) }}">{{ $m->displayName() }}</a>
                @endforeach
            </div>
        </details>
    @endif
@endsection
@section('content')
    <div class="entry-index">
        <nav class="tabs entry-index-tabs" id="entryTypeTabs">
            <div class="entry-type-track" id="entryTypeTrack">
                <a class="{{ ! $modelId ? 'active' : '' }}" href="{{ $qs(['model_id' => null, 'category_id' => null]) }}">{{ admin_text('全部内容') }}</a>
                @foreach($models as $m)
                    <a class="{{ (int) $modelId === (int) $m->id ? 'active' : '' }}" href="{{ $qs(['model_id' => $m->id, 'category_id' => null]) }}">{{ $m->displayName() }}</a>
                @endforeach
            </div>
            <details class="entry-type-more" id="entryTypeMore" hidden>
                <summary>{{ admin_text('更多') }}</summary>
                <div class="entry-type-more-list" id="entryTypeMoreList"></div>
            </details>
        </nav>

        <div class="entry-find">
            <form method="get" class="filter-bar" id="entryFindForm" action="{{ route('admin.entries.index') }}">
                @if($modelId)
                    <input type="hidden" name="model_id" value="{{ $modelId }}">
                @endif
                @if($status !== '')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                @if($tagId)
                    <input type="hidden" name="tag_id" value="{{ $tagId }}">
                @endif
                @if($flag !== '')
                    <input type="hidden" name="flag" value="{{ $flag }}">
                @endif
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ admin_text('搜索标题、正文或 ID') }}" aria-label="{{ admin_text('搜索内容') }}">
                <button class="btn btn-muted" type="submit">{{ admin_text('搜索') }}</button>
                @if($categories->isNotEmpty())
                    <span class="status-label">{{ admin_text('栏目') }}</span>
                    <select name="category_id" onchange="this.form.submit()" aria-label="{{ admin_text('栏目') }}">
                        <option value="">{{ admin_text('全部') }}</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected((int) $categoryId === (int) $c->id)>{{ $catLabel($c) }}</option>
                        @endforeach
                    </select>
                @endif
            </form>
            <div class="entry-status-row">
                <span class="status-label">{{ admin_text('状态') }}</span>
                <a class="chip {{ $status === '' ? 'active' : '' }}" href="{{ $qs(['status' => null]) }}">{{ admin_text('全部') }}</a>
                <a class="chip {{ $status === 'published' ? 'active' : '' }}" href="{{ $qs(['status' => 'published']) }}">{{ admin_text('已发布') }}</a>
                <a class="chip {{ $status === 'draft' ? 'active' : '' }}" href="{{ $qs(['status' => 'draft']) }}">{{ admin_text('草稿') }}</a>
                <a class="chip {{ $status === 'pending' ? 'active' : '' }}" href="{{ $qs(['status' => 'pending']) }}">{{ admin_text('待审') }}{{ ($pendingCount ?? 0) > 0 ? ' '.$pendingCount : '' }}</a>
                <a class="chip {{ $status === 'scheduled' ? 'active' : '' }}" href="{{ $qs(['status' => 'scheduled']) }}">{{ admin_text('定时') }}</a>
                @if(count(\App\Support\Locale::codes()) > 1)
                    <span class="status-label">{{ admin_text('译文') }}</span>
                    <a class="chip {{ $translation === 'missing_body' ? 'active' : '' }}" href="{{ $qs(['translation' => 'missing_body']) }}">{{ admin_text('正文没翻译') }}</a>
                    <a class="chip {{ $translation === 'fallback' ? 'active' : '' }}" href="{{ $qs(['translation' => 'fallback']) }}">{{ admin_text('正在使用默认语言') }}</a>
                    <a class="chip {{ $translation === 'stale' ? 'active' : '' }}" href="{{ $qs(['translation' => 'stale']) }}">{{ admin_text('译文待复核') }}</a>
                    <a class="chip {{ $translation === 'revision' ? 'active' : '' }}" href="{{ $qs(['translation' => 'revision']) }}">{{ admin_text('有修改稿') }}</a>
                @endif
                @if($filterTag ?? null)
                    <a class="chip active" href="{{ $qs(['tag_id' => null]) }}">{{ admin_text('标签') }} {{ $filterTag->uiTitle() }} ×</a>
                @endif
                @if($flag !== '')
                    <a class="chip active" href="{{ $qs(['flag' => null]) }}">{{ admin_text('属性') }} {{ $filterFlag ? $filterFlag->displayName() : $flag }} ×</a>
                @endif
                @if($hasFilters)
                    <a class="chip" href="{{ route('admin.entries.index', array_filter(['model_id' => $modelId])) }}">{{ admin_text('清除筛选') }}</a>
                @endif
            </div>
        </div>

        @if($entries->isNotEmpty())
            <form method="post" action="{{ route('admin.entries.batch') }}" id="batchForm">
                @csrf
                <input type="hidden" name="model_id" value="{{ $modelId }}">
                <input type="hidden" name="keep_status" value="{{ $status }}">
                <input type="hidden" name="keep_q" value="{{ $q }}">
                <input type="hidden" name="keep_category_id" value="{{ $categoryId }}">
                <input type="hidden" name="keep_tag_id" value="{{ $tagId }}">
                <input type="hidden" name="keep_flag" value="{{ $flag }}">
                <div class="batch-bar" id="batchBar" hidden>
                    <div class="batch-actions-main">
                        <strong id="batchCount">{{ admin_text('已选 :count 篇', ['count' => 0]) }}</strong>
                        @if($canPublish)
                            <button class="btn btn-sm" type="submit" name="action" value="publish">{{ admin_text('发布到前台') }}</button>
                        @endif
                        <button class="btn btn-sm btn-danger" type="submit" name="action" value="delete">{{ admin_text('移入回收站') }}</button>
                    </div>
                    <details class="batch-more">
                        <summary>{{ admin_text('更多操作') }}</summary>
                        <div class="batch-actions-extra">
                            <button class="btn btn-sm btn-muted" type="submit" name="action" value="pending">{{ admin_text('改为待审') }}</button>
                            <button class="btn btn-sm btn-muted" type="submit" name="action" value="draft">{{ admin_text('改回草稿') }}</button>
                            @if($categories->isNotEmpty())
                                <select name="category_id" id="batchCategory" aria-label="{{ admin_text('目标栏目') }}">
                                    <option value="">{{ admin_text('选择栏目') }}</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}">{{ $catLabel($c) }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-muted" type="submit" name="action" value="move">{{ admin_text('移动过去') }}</button>
                                <button class="btn btn-sm btn-muted" type="submit" name="action" value="copy">{{ admin_text('复制一份') }}</button>
                            @endif
                            @if($recommends->isNotEmpty())
                                <select name="recommend_id" id="batchRecommend" aria-label="{{ admin_text('推荐位') }}">
                                    <option value="">{{ admin_text('选择推荐位') }}</option>
                                    @foreach($recommends as $r)
                                        <option value="{{ $r->id }}">{{ $r->title }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-muted" type="submit" name="action" value="push">{{ admin_text('推送到推荐位') }}</button>
                            @endif
                        </div>
                    </details>
                </div>
            </form>
        @endif

        @if($entries->isEmpty())
            <div class="list-empty">
                @if($hasFilters)
                    <p>{{ admin_text('没有符合条件的内容。') }}</p>
                    <a class="btn btn-muted" href="{{ route('admin.entries.index', array_filter(['model_id' => $modelId])) }}">{{ admin_text('清除筛选') }}</a>
                @else
                    <p>{{ admin_text('还没有内容。') }}</p>
                    @if($currentModel)
                        <a class="btn" href="{{ route('admin.entries.create', ['model_id' => $currentModel->id]) }}">{{ $createLabel }}</a>
                    @elseif($models->isNotEmpty())
                        <p class="muted">{{ admin_text('点右上角「新建」，先选要写的类型。') }}</p>
                    @endif
                @endif
            </div>
        @else
            <table class="data">
                <thead>
                <tr>
                    <th style="width:36px"><input type="checkbox" id="checkAll" aria-label="{{ admin_text('全选本页') }}" title="{{ admin_text('勾选后可批量处理') }}"></th>
                    <th>{{ admin_text('标题') }}</th>
                    <th style="width:120px">{{ admin_text('时间') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($entries as $entry)
                    @php
                        $flagNames = array_values(array_filter(array_map('trim', explode(',', (string) $entry->flags))));
                        $when = $entry->isScheduled() ? $entry->published_at : $entry->updated_at;
                        $canFront = $entry->isLive() && $entry->url !== '#';
                    @endphp
                    <tr>
                        <td><input type="checkbox" form="batchForm" name="ids[]" value="{{ $entry->id }}" class="row-check" title="{{ admin_text('勾选后批量处理') }}"></td>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.entries.edit', $entry) }}">{{ $entry->uiTitle() ?: admin_text('无标题') }}</a>
                                @unless($entry->isLive())
                                    @if($entry->isPending())
                                        <span class="badge badge-warn">{{ admin_text('待审') }}</span>
                                    @elseif($entry->isScheduled())
                                        <span class="badge badge-warn">{{ admin_text('定时') }}</span>
                                    @else
                                        <span class="badge badge-off">{{ \App\Models\Cms\Entry::statusLabel($entry->status) }}</span>
                                    @endif
                                @endunless
                            </div>
                            <div class="entry-row-meta">
                                @if(! $modelId && $entry->modelDef?->name)
                                    {{ $entry->modelDef->displayName() }} ·
                                @endif
                                {{ $entry->category?->displayTitle() ?: admin_text('未分栏') }}
                                · #{{ $entry->id }}
                                @if($entry->member?->name)
                                    · {{ admin_text('投稿') }} {{ $entry->member->displayName() }}
                                @endif
                            </div>
                            @if($entry->is_homepage || $flagNames !== [])
                                <div class="entry-row-tags">
                                    @if($entry->is_homepage)
                                        <span class="badge badge-search">{{ admin_text('首页') }}</span>
                                    @endif
                                    @foreach($flagNames as $flag)
                                        <span class="badge">{{ $flagOptions[$flag] ?? $flag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="muted" title="{{ $when }}">{{ $fmtTime($when) }}</td>
                        <td class="actions">
                            @if($canFront)
                                <a class="btn btn-sm btn-muted" href="{{ url($entry->url) }}" target="_blank" rel="noopener">{{ admin_text('前台') }}</a>
                            @endif
                            @if($canPublish && in_array($entry->status, ['draft', 'pending'], true))
                                <form action="{{ route('admin.entries.batch') }}" method="post" onsubmit="return confirm(@js(admin_text('确认发布「:name」？', ['name' => $entry->uiTitle()])))">
                                    @csrf
                                    <input type="hidden" name="action" value="publish">
                                    <input type="hidden" name="ids[]" value="{{ $entry->id }}">
                                    <input type="hidden" name="model_id" value="{{ $modelId }}">
                                    <input type="hidden" name="keep_status" value="{{ $status }}">
                                    <input type="hidden" name="keep_q" value="{{ $q }}">
                                    <input type="hidden" name="keep_category_id" value="{{ $categoryId }}">
                                    <button class="btn btn-sm" type="submit">{{ admin_text('发布') }}</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.entries.destroy', $entry) }}" method="post" onsubmit="return confirm(@js(admin_text('移入回收站？')))">
                                @csrf @method('DELETE')
                                <button class="btn-quiet" type="submit">{{ admin_text('回收站') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 篇', ['count' => $entries->total()]) }}</p>
            {{ $entries->links() }}
        @endif
    </div>
@endsection
@push('scripts')
<script>
(function () {
    var nav = document.getElementById('entryTypeTabs');
    var track = document.getElementById('entryTypeTrack');
    var more = document.getElementById('entryTypeMore');
    var list = document.getElementById('entryTypeMoreList');
    if (nav && track && more && list) {
        var layout = function () {
            var tabs = Array.prototype.slice.call(track.querySelectorAll('a'));
            tabs.forEach(function (t) { t.hidden = false; });
            list.innerHTML = '';
            more.hidden = true;
            more.removeAttribute('open');
            more.classList.remove('has-active');
            if (track.scrollWidth <= track.clientWidth + 1) return;
            more.hidden = false;
            void nav.offsetWidth;
            var overflow = [];
            var hideFrom = function (allowActive) {
                var i = tabs.length - 1;
                while (i >= 1 && track.scrollWidth > track.clientWidth + 1) {
                    if (tabs[i].hidden || (!allowActive && tabs[i].classList.contains('active'))) {
                        i--;
                        continue;
                    }
                    tabs[i].hidden = true;
                    overflow.unshift(tabs[i]);
                    i--;
                }
            };
            hideFrom(false);
            hideFrom(true);
            overflow.forEach(function (t) {
                var a = document.createElement('a');
                a.href = t.getAttribute('href') || '#';
                a.textContent = t.textContent;
                if (t.classList.contains('active')) a.className = 'active';
                list.appendChild(a);
            });
            if (!overflow.length) {
                more.hidden = true;
                return;
            }
            if (overflow.some(function (t) { return t.classList.contains('active'); })) {
                more.classList.add('has-active');
            }
        };
        var scheduled = false;
        var schedule = function () {
            if (scheduled) return;
            scheduled = true;
            requestAnimationFrame(function () {
                scheduled = false;
                layout();
            });
        };
        layout();
        window.addEventListener('resize', schedule);
        if (window.ResizeObserver) new ResizeObserver(schedule).observe(nav);
    }
})();
(function () {
    var checkAll = document.getElementById('checkAll');
    var boxes = function () { return document.querySelectorAll('.row-check'); };
    var bar = document.getElementById('batchBar');
    var count = document.getElementById('batchCount');
    var cat = document.getElementById('batchCategory');
    var rec = document.getElementById('batchRecommend');
    var labels = {
        publish: @js(admin_text('发布到前台')),
        pending: @js(admin_text('改为待审')),
        draft: @js(admin_text('改回草稿')),
        move: @js(admin_text('移动到所选栏目')),
        copy: @js(admin_text('复制一份')),
        push: @js(admin_text('推到所选推荐位')),
        delete: @js(admin_text('移入回收站'))
    };

    function selected() {
        return Array.prototype.filter.call(boxes(), function (c) { return c.checked; });
    }
    function syncCount() {
        var n = selected().length;
        if (count) count.textContent = @js(admin_text('已选 :count 篇')).replace(':count', n);
        if (bar) bar.hidden = n === 0;
        if (checkAll && boxes().length) {
            checkAll.checked = n === boxes().length;
            checkAll.indeterminate = n > 0 && n < boxes().length;
        }
    }
    checkAll?.addEventListener('change', function () {
        boxes().forEach(function (c) { c.checked = checkAll.checked; });
        syncCount();
    });
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('row-check')) syncCount();
    });
    document.getElementById('batchForm')?.addEventListener('submit', function (e) {
        var n = selected().length;
        if (n === 0) {
            e.preventDefault();
            alert(@js(admin_text('请先勾选要处理的内容')));
            return;
        }
        var v = (e.submitter && e.submitter.name === 'action') ? e.submitter.value : '';
        if (v === 'move' && cat && !cat.value) {
            e.preventDefault();
            alert(@js(admin_text('请先选择要换到的栏目，再点「移动」')));
            return;
        }
        if (v === 'push' && rec && !rec.value) {
            e.preventDefault();
            alert(@js(admin_text('请先选择推荐位，再点「推送」')));
            return;
        }
        var name = labels[v] || @js(admin_text('这项操作'));
        var message = @js(admin_text('将对已选的 :count 篇执行「:action」，确定吗？'));
        if (!confirm(message.replace(':count', n).replace(':action', name))) e.preventDefault();
    });
    syncCount();
})();
</script>
@endpush
