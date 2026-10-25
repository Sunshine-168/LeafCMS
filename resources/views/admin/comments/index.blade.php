@extends('admin.layout')
@section('title', admin_t('nav.comments'))
@section('icon', 'comments')
@php
    $q = $q ?? '';
    $status = $status ?? 'pending';
    $entryId = $entryId ?? null;
    $pendingCount = $pendingCount ?? 0;
    $filterEntry = $filterEntry ?? null;
    $hasFilters = $q !== '' || $entryId;
    $qs = function (array $extra = []) use ($q, $status, $entryId) {
        return route('admin.comments.index', array_filter(
            array_merge(['q' => $q, 'status' => $status, 'entry_id' => $entryId], $extra),
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
@endphp
@section('header_actions')
    @if($pendingCount > 0)
        <span class="badge badge-warn">{{ admin_text('待审') }} {{ $pendingCount }}</span>
    @endif
@endsection
@section('content')
    <div class="entry-index comment-index">
        <div class="entry-find">
            <form method="get" class="filter-bar" id="commentFindForm" action="{{ route('admin.comments.index') }}">
                @if($status !== '' && $status !== 'pending')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                @if($entryId)
                    <input type="hidden" name="entry_id" value="{{ $entryId }}">
                @endif
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ admin_text('搜索评论或作者') }}" aria-label="{{ admin_text('搜索评论') }}">
                <button class="btn btn-muted" type="submit">{{ admin_text('搜索') }}</button>
            </form>
            <div class="entry-status-row">
                <span class="status-label">{{ admin_text('状态') }}</span>
                <a class="chip {{ $status === 'pending' ? 'active' : '' }}" href="{{ $qs(['status' => 'pending']) }}">{{ admin_text('待审') }}{{ $pendingCount > 0 ? ' '.$pendingCount : '' }}</a>
                <a class="chip {{ $status === 'approved' ? 'active' : '' }}" href="{{ $qs(['status' => 'approved']) }}">{{ admin_text('已通过') }}</a>
                <a class="chip {{ $status === 'rejected' ? 'active' : '' }}" href="{{ $qs(['status' => 'rejected']) }}">{{ admin_text('已拒绝') }}</a>
                <a class="chip {{ $status === 'spam' ? 'active' : '' }}" href="{{ $qs(['status' => 'spam']) }}">{{ admin_text('垃圾') }}</a>
                <a class="chip {{ $status === 'all' ? 'active' : '' }}" href="{{ $qs(['status' => 'all']) }}">{{ admin_text('全部') }}</a>
                @if($filterEntry)
                    <a class="chip active" href="{{ $qs(['entry_id' => null]) }}">{{ \Illuminate\Support\Str::limit($filterEntry->t('title') ?: admin_text('这篇内容'), 16) }} ×</a>
                @endif
                @if($hasFilters)
                    <a class="chip" href="{{ $qs(['q' => null, 'entry_id' => null]) }}">{{ admin_text('清除筛选') }}</a>
                @endif
            </div>
        </div>

        @if($comments->isNotEmpty())
            <form method="post" action="{{ route('admin.comments.batch') }}" id="batchForm">
                @csrf
                <div class="batch-bar" id="batchBar" hidden>
                    <div class="batch-actions-main">
                        <strong id="batchCount">{{ admin_text('已选 :count 条', ['count' => 0]) }}</strong>
                        <button class="btn btn-sm" type="submit" name="status" value="approved">{{ admin_text('通过') }}</button>
                        <button class="btn btn-sm btn-muted" type="submit" name="status" value="rejected">{{ admin_text('拒绝') }}</button>
                    </div>
                    <details class="batch-more">
                        <summary>{{ admin_text('更多操作') }}</summary>
                        <div class="batch-actions-extra">
                            <button class="btn btn-sm btn-muted" type="submit" name="status" value="spam">{{ admin_text('标为垃圾') }}</button>
                            <button class="btn btn-sm btn-muted" type="submit" name="status" value="pending">{{ admin_text('改回待审') }}</button>
                        </div>
                    </details>
                </div>
            </form>
        @endif

        @if($comments->isEmpty())
            <div class="list-empty">
                @if($hasFilters)
                    <p>{{ admin_text('没有符合条件的评论。') }}</p>
                    <a class="btn btn-muted" href="{{ $qs(['q' => null, 'entry_id' => null]) }}">{{ admin_text('清除筛选') }}</a>
                @elseif($status === 'pending')
                    <p>{{ admin_text('当前没有待审评论。') }}</p>
                    <p class="muted">{{ admin_text('前台新评论会排在这里。通过后才会显示在文章下。') }}</p>
                    <a class="chip" href="{{ $qs(['status' => 'all']) }}">{{ admin_text('查看全部评论') }}</a>
                @else
                    <p>{{ admin_text('这一栏还没有评论。') }}</p>
                    <a class="btn btn-muted" href="{{ route('admin.comments.index') }}">{{ admin_text('回到待审') }}</a>
                @endif
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('点勾选可批量处理。通过后评论会出现在对应内容下。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th style="width:36px"><input type="checkbox" id="checkAll" aria-label="{{ admin_text('全选本页') }}" title="{{ admin_text('勾选后可批量处理') }}"></th>
                    <th>{{ admin_text('评论') }}</th>
                    <th style="width:140px">{{ admin_text('时间') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($comments as $row)
                    <tr @class(['is-pending' => $row->status === 'pending'])>
                        <td><input type="checkbox" form="batchForm" name="ids[]" value="{{ $row->id }}" class="row-check" title="{{ admin_text('勾选后批量处理') }}"></td>
                        <td>
                            <div class="entry-row-title-line">
                                <span class="comment-excerpt">{{ $row->content }}</span>
                                @if($status === 'all' || $row->status !== $status)
                                    @if($row->status === 'pending')
                                        <span class="badge badge-warn">{{ admin_text('待审') }}</span>
                                    @elseif($row->status === 'approved')
                                        <span class="badge badge-ok">{{ admin_text('已通过') }}</span>
                                    @elseif($row->status === 'spam')
                                        <span class="badge">{{ admin_text('垃圾') }}</span>
                                    @else
                                        <span class="badge badge-off">{{ admin_text(\App\Models\Cms\Comment::statusLabel($row->status)) }}</span>
                                    @endif
                                @endif
                            </div>
                            <div class="entry-row-meta">
                                {{ $row->author_name ?: admin_text('匿名') }}
                                @if($row->author_email)
                                    · {{ $row->author_email }}
                                @endif
                                @if($row->entry)
                                    ·
                                    @if($row->entry->url && $row->entry->url !== '#')
                                        <a href="{{ url($row->entry->url) }}" target="_blank" rel="noopener">{{ $row->entry->t('title') ?: admin_text('无标题') }}</a>
                                    @else
                                        {{ $row->entry->t('title') ?: admin_text('无标题') }}
                                    @endif
                                @endif
                                @if($row->parent_id)
                                    · {{ admin_text('回复') }}
                                @endif
                                @if($row->ip)
                                    · {{ $row->ip }}
                                @endif
                            </div>
                        </td>
                        <td class="muted" title="{{ $row->created_at }}">{{ $fmtTime($row->created_at) }}</td>
                        <td class="actions">
                            @if($row->status !== 'approved')
                                <form action="{{ route('admin.comments.moderate', $row) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm" type="submit">{{ admin_text('通过') }}</button>
                                </form>
                            @endif
                            @if($row->status !== 'rejected')
                                <form action="{{ route('admin.comments.moderate', $row) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-sm btn-muted" type="submit">{{ admin_text('拒绝') }}</button>
                                </form>
                            @endif
                            <details class="create-menu">
                                <summary class="btn btn-sm btn-muted">{{ admin_text('更多') }}</summary>
                                <div class="create-menu-list">
                                    @if($row->status !== 'spam')
                                        <form action="{{ route('admin.comments.moderate', $row) }}" method="post">
                                            @csrf
                                            <input type="hidden" name="status" value="spam">
                                            <button type="submit">{{ admin_text('标为垃圾') }}</button>
                                        </form>
                                    @endif
                                    @if($row->status !== 'pending')
                                        <form action="{{ route('admin.comments.moderate', $row) }}" method="post">
                                            @csrf
                                            <input type="hidden" name="status" value="pending">
                                            <button type="submit">{{ admin_text('改回待审') }}</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.comments.destroy', $row) }}" method="post" onsubmit="return confirm(@js(admin_text('确定删除这条评论？')))">
                                        @csrf @method('DELETE')
                                        <button class="is-danger" type="submit">{{ admin_text('删除') }}</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 条', ['count' => $comments->total()]) }}</p>
            {{ $comments->links() }}
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
    var labels = {
        approved: @json(admin_text('通过')),
        rejected: @json(admin_text('拒绝')),
        spam: @json(admin_text('标为垃圾')),
        pending: @json(admin_text('改回待审'))
    };
    var selectedLabel = @json(admin_text('已选 :count 条'));
    var needPick = @json(admin_text('请先勾选要处理的评论'));
    var fallbackAction = @json(admin_text('这项操作'));
    var confirmTpl = @json(admin_text('将对已选的 :count 条执行「:action」，确定吗？'));
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
        var v = (e.submitter && e.submitter.name === 'status') ? e.submitter.value : '';
        var name = labels[v] || fallbackAction;
        if (!confirm(confirmTpl.replace(':count', n).replace(':action', name))) e.preventDefault();
    });
    syncCount();
})();
</script>
@endpush
