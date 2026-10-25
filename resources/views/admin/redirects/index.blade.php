@extends('admin.layout')
@section('title', admin_t('nav.redirects'))
@section('icon', 'exchange-alt')
@php
    $q = $q ?? '';
    $hasFilters = $q !== '';
    $composeFocus = session('redirect_quick_added') || ($redirects->isEmpty() && ! $hasFilters);
@endphp
@section('content')
    <div class="entry-index redirect-index">
        <div class="redirect-compose">
            <form method="post" action="{{ route('admin.redirects.store') }}" class="redirect-compose-form">
                @csrf
                <input type="hidden" name="keep_q" value="{{ $q }}">
                <input type="hidden" name="status_code" value="301">
                <input type="hidden" name="is_active" value="1">
                <label class="redirect-compose-label" for="redirect-quick-from">{{ admin_text('添加跳转') }}</label>
                <div class="redirect-compose-row">
                    <input id="redirect-quick-from" type="text" name="source_path" value="" placeholder="{{ admin_text('旧地址，如 /old-news') }}" aria-label="{{ admin_text('旧地址') }}" required @if($composeFocus) autofocus @endif>
                    <span class="redirect-arrow" aria-hidden="true">→</span>
                    <input type="text" name="target_url" value="" placeholder="{{ admin_text('新地址，如 /news') }}" aria-label="{{ admin_text('新地址') }}" required>
                    <button class="btn" type="submit">{{ admin_text('添加') }}</button>
                </div>
                <p class="muted field-hint">{!! str_replace(':form', '<a href="'.e(route('admin.redirects.create')).'">'.e(admin_text('打开完整表单')).'</a>', e(admin_text('访客打开旧地址会跳到新地址。默认永久搬走。需要临时跳转或加备注时，:form。'))) !!}</p>
            </form>
        </div>

        @if($hasFilters || $redirects->isNotEmpty())
            <form method="get" class="filter-bar redirect-find" action="{{ route('admin.redirects.index') }}">
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ admin_text('搜索旧地址、新地址或备注') }}" aria-label="{{ admin_text('搜索跳转') }}">
                <button class="btn btn-muted" type="submit">{{ admin_text('搜索') }}</button>
                @if($hasFilters)
                    <a class="chip" href="{{ route('admin.redirects.index') }}">{{ admin_text('清除筛选') }}</a>
                @endif
            </form>
        @endif

        @if($redirects->isEmpty())
            <div class="list-empty">
                @if($hasFilters)
                    <p>{{ admin_text('没有符合条件的跳转。') }}</p>
                    <a class="btn btn-muted" href="{{ route('admin.redirects.index') }}">{{ admin_text('清除筛选') }}</a>
                @else
                    <p>{{ admin_text('还没有跳转。') }}</p>
                    <p class="muted">{{ admin_text('改了栏目或网址后，把旧地址指到新地址，收藏夹和搜索结果还能打开。') }}</p>
                @endif
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('点旧地址去改。停用后访客打开旧地址不再跳转，规则还留着。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('跳转') }}</th>
                    <th style="width:110px">{{ admin_text('走过') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($redirects as $row)
                    <tr @class(['is-off' => ! $row->is_active])>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.redirects.edit', $row) }}">{{ $row->source_path }}</a>
                                <span class="redirect-arrow">→</span>
                                <span class="redirect-target">{{ $row->target_url }}</span>
                                @if($row->isTemporary())
                                    <span class="badge">{{ admin_text('临时') }}</span>
                                @endif
                                @unless($row->is_active)
                                    <span class="badge badge-off">{{ admin_text('停用') }}</span>
                                @endunless
                            </div>
                            @if($row->note)
                                <div class="entry-row-meta">{{ $row->note }}</div>
                            @endif
                        </td>
                        <td>
                            @if($row->hits > 0)
                                {{ admin_text(':count 次', ['count' => $row->hits]) }}
                                @if($row->last_hit_at)
                                    <div class="entry-row-meta">{{ $row->last_hit_at->format('m-d H:i') }}</div>
                                @endif
                            @else
                                <span class="muted">{{ admin_text('还没走过') }}</span>
                            @endif
                        </td>
                        <td class="actions">
                            <form action="{{ route('admin.redirects.toggle', $row) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-muted" type="submit">{{ $row->is_active ? admin_text('停用') : admin_text('启用') }}</button>
                            </form>
                            <form action="{{ route('admin.redirects.destroy', $row) }}" method="post" onsubmit="return confirm(@js(admin_text('确定删除「:from → :to」？访客再打开旧地址就不会跳了。', ['from' => $row->source_path, 'to' => $row->target_url])))">
                                @csrf @method('DELETE')
                                <input type="hidden" name="keep_q" value="{{ $q }}">
                                <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 条', ['count' => $redirects->total()]) }}</p>
            {{ $redirects->links() }}
        @endif
    </div>
@endsection
