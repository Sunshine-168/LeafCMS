@extends('admin.layout')
@section('title', admin_t('nav.members'))
@section('icon', 'users')
@php
    $q = $q ?? '';
    $groupKey = $groupKey ?? '';
    $inactiveOnly = (bool) ($inactiveOnly ?? false);
    $hasFilters = $q !== '' || $groupKey !== '' || $inactiveOnly;
    $qs = function (array $extra = []) use ($q, $groupKey, $inactiveOnly) {
        return route('admin.members.index', array_filter(
            array_merge([
                'q' => $q,
                'group' => $groupKey !== '' ? $groupKey : null,
                'off' => $inactiveOnly ? '1' : null,
            ], $extra),
            fn ($v) => $v !== '' && $v !== null
        ));
    };
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.member-groups.index') }}">{{ admin_text('分组') }}</a>
    <a class="btn btn-sm" href="{{ route('admin.members.create') }}"><i class="fas fa-plus"></i> {{ admin_text('新建会员') }}</a>
@endsection
@section('content')
    <div class="entry-index member-index">
        <div class="entry-find">
            <form method="get" class="filter-bar member-find" action="{{ route('admin.members.index') }}">
                @if($groupKey !== '')
                    <input type="hidden" name="group" value="{{ $groupKey }}">
                @endif
                @if($inactiveOnly)
                    <input type="hidden" name="off" value="1">
                @endif
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ admin_text('搜索姓名、邮箱或手机') }}" aria-label="{{ admin_text('搜索会员') }}">
                <button class="btn btn-muted" type="submit">{{ admin_text('搜索') }}</button>
            </form>
            <div class="entry-status-row">
                <a class="chip {{ $groupKey === '' && ! $inactiveOnly ? 'active' : '' }}" href="{{ route('admin.members.index', array_filter(['q' => $q ?: null])) }}">{{ admin_text('全部') }}</a>
                @foreach($groups as $g)
                    <a class="chip {{ $groupKey === (string) $g->id ? 'active' : '' }}" href="{{ $qs(['group' => (string) $g->id, 'off' => null]) }}">{{ $g->displayName() }}</a>
                @endforeach
                <a class="chip {{ $groupKey === 'none' ? 'active' : '' }}" href="{{ $qs(['group' => 'none', 'off' => null]) }}">{{ admin_text('未分组') }}</a>
                <a class="chip {{ $inactiveOnly ? 'active' : '' }}" href="{{ $qs(['off' => $inactiveOnly ? null : '1']) }}">{{ admin_text('已停用') }}</a>
                @if($hasFilters)
                    <a class="chip" href="{{ route('admin.members.index') }}">{{ admin_text('清除筛选') }}</a>
                @endif
                @if($members->isNotEmpty())
                    <span class="muted entry-find-hint">{{ admin_text('点姓名去改资料。停用后不能登录前台，已留下的评论和稿还在。') }}</span>
                @endif
            </div>
        </div>

        @if($members->isEmpty())
            <div class="list-empty">
                @if($hasFilters)
                    <p>{{ admin_text('没有符合条件的会员。') }}</p>
                    <a class="btn btn-muted" href="{{ route('admin.members.index') }}">{{ admin_text('清除筛选') }}</a>
                @else
                    <p>{{ admin_text('还没有会员。') }}</p>
                    <p class="muted">{{ admin_text('前台注册或这里添加。他们登录的是网站，不是后台。') }}</p>
                    <a class="btn" href="{{ route('admin.members.create') }}">{{ admin_text('新建会员') }}</a>
                @endif
            </div>
        @else
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('会员') }}</th>
                    <th style="width:140px">{{ admin_text('分组') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($members as $member)
                    <tr @class(['is-off' => ! $member->is_active])>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.members.edit', $member) }}">{{ $member->displayName() ?: admin_text('未命名') }}</a>
                                @unless($member->is_active)
                                    <span class="badge badge-off">{{ admin_text('停用') }}</span>
                                @endunless
                            </div>
                            <div class="entry-row-meta">
                                {{ $member->email }}
                                @if($member->created_at)
                                    · {{ admin_text('加入 :date', ['date' => $member->created_at->format('Y-m-d')]) }}
                                @endif
                                @if(($member->contributed_entries_count ?? 0) > 0)
                                    · {{ admin_text(':count 篇投稿', ['count' => $member->contributed_entries_count]) }}
                                @endif
                            </div>
                        </td>
                        <td>{{ $member->group?->displayName() ?: admin_text('未分组') }}</td>
                        <td class="actions">
                            <form action="{{ route('admin.members.toggle', $member) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-muted" type="submit">{{ $member->is_active ? admin_text('停用') : admin_text('启用') }}</button>
                            </form>
                            <form action="{{ route('admin.members.destroy', $member) }}" method="post" onsubmit="return confirm(@js(admin_text('确定删除「:name」？不能再登录，已留下的评论和稿还在。', ['name' => $member->displayName() ?: $member->email])))">
                                @csrf @method('DELETE')
                                <input type="hidden" name="keep_q" value="{{ $q }}">
                                <input type="hidden" name="keep_group" value="{{ $groupKey }}">
                                <input type="hidden" name="keep_off" value="{{ $inactiveOnly ? '1' : '' }}">
                                <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 人', ['count' => $members->total()]) }}</p>
            {{ $members->links() }}
        @endif
    </div>
@endsection
