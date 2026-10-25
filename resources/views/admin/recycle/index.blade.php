@extends('admin.layout')
@section('title', admin_t('nav.recycle'))
@section('icon', 'trash-alt')
@php
    $q = $q ?? '';
    $modelId = $modelId ?? null;
    $hasFilters = $q !== '' || $modelId;
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
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.entries.index') }}">{{ admin_back('entries') }}</a>
    @if($entries->total() > 0)
        <form action="{{ route('admin.recycle.empty') }}" method="post" onsubmit="return confirm(@js(admin_text('将彻底删除回收站里的 :count 篇，不可恢复。确定清空？', ['count' => $entries->total()])))">
            @csrf
            <button class="btn btn-sm btn-danger" type="submit">{{ admin_text('清空回收站') }}</button>
        </form>
    @endif
@endsection
@section('content')
    <div class="entry-index recycle-index">
        @if($entries->total() > 0 || $hasFilters)
            <form method="get" class="filter-bar" action="{{ route('admin.recycle.index') }}">
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ admin_text('搜索已删内容的标题或 ID') }}" aria-label="{{ admin_text('搜索回收站') }}">
                @if($models->isNotEmpty())
                    <select name="model_id" onchange="this.form.submit()" aria-label="{{ admin_text('类型') }}">
                        <option value="">{{ admin_text('全部类型') }}</option>
                        @foreach($models as $m)
                            <option value="{{ $m->id }}" @selected((int) $modelId === (int) $m->id)>{{ $m->displayName() }}</option>
                        @endforeach
                    </select>
                @endif
                <button class="btn btn-muted" type="submit">{{ admin_text('搜索') }}</button>
                @if($hasFilters)
                    <a class="chip" href="{{ route('admin.recycle.index') }}">{{ admin_text('清除筛选') }}</a>
                @endif
            </form>
        @endif

        @if($entries->isEmpty())
            <div class="list-empty">
                @if($hasFilters)
                    <p>{{ admin_text('没有符合条件的已删内容。') }}</p>
                    <a class="btn btn-muted" href="{{ route('admin.recycle.index') }}">{{ admin_text('清除筛选') }}</a>
                @else
                    <p>{{ admin_text('回收站是空的。') }}</p>
                    <p class="muted">{{ admin_text('从内容里删除的稿件会先放在这里，可以随时还原。不会自动清空。') }}</p>
                    <a class="btn btn-muted" href="{{ route('admin.entries.index') }}">{{ admin_text('去内容') }}</a>
                @endif
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('还原后回到内容，状态与栏目保持删除前的样子。彻底删除无法恢复。') }}</p>
            <form method="post" action="{{ route('admin.recycle.batch') }}" id="batchForm">
                @csrf
                <input type="hidden" name="keep_q" value="{{ $q }}">
                <input type="hidden" name="keep_model_id" value="{{ $modelId }}">
                <div class="batch-bar" id="batchBar" hidden>
                    <div class="batch-actions-main">
                        <strong id="batchCount">{{ admin_text('已选 :count 篇', ['count' => 0]) }}</strong>
                        <button class="btn btn-sm" type="submit" name="action" value="restore">{{ admin_text('还原所选') }}</button>
                        <button class="btn btn-sm btn-danger" type="submit" name="action" value="delete">{{ admin_text('彻底删除所选') }}</button>
                    </div>
                </div>
            </form>
            <table class="data">
                <thead>
                <tr>
                    <th style="width:36px"><input type="checkbox" id="checkAll" aria-label="{{ admin_text('全选本页') }}" title="{{ admin_text('勾选后批量处理') }}"></th>
                    <th>{{ admin_text('标题') }}</th>
                    <th style="width:140px">{{ admin_text('删除时间') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td><input type="checkbox" form="batchForm" name="ids[]" value="{{ $entry->id }}" class="row-check" title="{{ admin_text('勾选后批量处理') }}"></td>
                        <td>
                            <div class="entry-row-title">{{ $entry->t('title') ?: admin_text('无标题') }}</div>
                            <div class="entry-row-meta">
                                {{ $entry->modelDef?->displayName() ?: admin_text('未知类型') }}
                                · {{ $entry->category?->t('title') ?: admin_text('未分栏') }}
                                · #{{ $entry->id }}
                            </div>
                        </td>
                        <td class="muted" title="{{ $entry->deleted_at }}">{{ $fmtTime($entry->deleted_at) }}</td>
                        <td class="actions">
                            <form action="{{ route('admin.recycle.restore', $entry->id) }}" method="post">
                                @csrf
                                <input type="hidden" name="keep_q" value="{{ $q }}">
                                <input type="hidden" name="keep_model_id" value="{{ $modelId }}">
                                <button class="btn btn-sm" type="submit">{{ admin_text('还原') }}</button>
                            </form>
                            <form action="{{ route('admin.recycle.force', $entry->id) }}" method="post" onsubmit="return confirm(@js(admin_text('彻底删除「:name」后无法恢复，确定？', ['name' => $entry->t('title') ?: admin_text('无标题')])))">
                                @csrf @method('DELETE')
                                <input type="hidden" name="keep_q" value="{{ $q }}">
                                <input type="hidden" name="keep_model_id" value="{{ $modelId }}">
                                <button class="btn btn-sm btn-danger" type="submit">{{ admin_text('彻底删除') }}</button>
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
    var checkAll = document.getElementById('checkAll');
    var boxes = function () { return document.querySelectorAll('.row-check'); };
    var bar = document.getElementById('batchBar');
    var count = document.getElementById('batchCount');
    var selectedLabel = @json(admin_text('已选 :count 篇'));
    var needPick = @json(admin_text('请先勾选内容'));
    var deleteTpl = @json(admin_text('将彻底删除已选的 :count 篇，无法恢复。确定吗？'));
    var restoreTpl = @json(admin_text('还原已选的 :count 篇到内容？'));
    function selected() {
        return Array.prototype.filter.call(boxes(), function (c) { return c.checked; });
    }
    function syncCount() {
        var n = selected().length;
        if (count) count.textContent = selectedLabel.replace(':count', n);
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
            alert(needPick);
            return;
        }
        var v = (e.submitter && e.submitter.name === 'action') ? e.submitter.value : '';
        if (v === 'delete') {
            if (!confirm(deleteTpl.replace(':count', n))) e.preventDefault();
            return;
        }
        if (!confirm(restoreTpl.replace(':count', n))) e.preventDefault();
    });
    syncCount();
})();
</script>
@endpush
