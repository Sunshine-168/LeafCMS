@extends('admin.layout')
@section('title', admin_t('nav.groups'))
@section('icon', 'user-tag')
@php
    $composeFocus = session('group_quick_added') || $groups->isEmpty();
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.members.index') }}">{{ admin_text('会员') }}</a>
@endsection
@section('content')
    <div class="entry-index member-group-index">
        <div class="member-group-compose">
            <form method="post" action="{{ route('admin.member-groups.store') }}">
                @csrf
                <label class="member-group-compose-label" for="group-quick-name">{{ admin_text('新增分组') }}</label>
                <div class="member-group-compose-row">
                    <input id="group-quick-name" type="text" name="name" value="" placeholder="{{ admin_text('显示名，如 普通会员') }}" aria-label="{{ admin_text('分组名称') }}" required @if($composeFocus) autofocus @endif>
                    <input class="group-key" type="text" name="slug" value="" placeholder="{{ admin_text('标识码，如 default') }}" aria-label="{{ admin_text('标识码') }}">
                    <button class="btn" type="submit">{{ admin_text('添加') }}</button>
                </div>
                <p class="muted field-hint">{!! str_replace(':form', '<a href="'.e(route('admin.member-groups.create')).'">'.e(admin_text('打开完整表单')).'</a>', e(admin_text('决定会员在前台能投稿、下载还是只看看。添加后点名称去勾权限。需要一次勾好时，:form。'))) !!}</p>
            </form>
        </div>

        @if($groups->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有分组。') }}</p>
                <p class="muted">{{ admin_text('在上方填写名称即可添加。会员按组决定能做什么。') }}</p>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('点名称去改权限。停用后这组前台能力关掉，人还在名单里。删掉后会员变成未分组。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('分组') }}</th>
                    <th style="width:120px">{{ admin_text('会员') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($groups as $i => $g)
                    <tr @class(['is-off' => ! $g->is_active])>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.member-groups.edit', $g) }}">{{ $g->displayName() ?: admin_text('未命名') }}</a>
                                @unless($g->is_active)
                                    <span class="badge badge-off">{{ admin_text('停用') }}</span>
                                @endunless
                            </div>
                            <div class="entry-row-meta">{{ $g->slug }}</div>
                        </td>
                        <td>
                            @if(($g->members_count ?? 0) > 0)
                                <a href="{{ route('admin.members.index', ['group' => $g->id]) }}">{{ admin_text(':count 人', ['count' => $g->members_count]) }}</a>
                            @else
                                <span class="muted">{{ admin_text('还没人') }}</span>
                            @endif
                        </td>
                        <td class="actions">
                            @if($i > 0)
                                <form action="{{ route('admin.member-groups.move', $g) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="dir" value="up">
                                    <button class="btn btn-sm btn-muted" type="submit">{{ admin_text('上移') }}</button>
                                </form>
                            @endif
                            @if($i < $groups->count() - 1)
                                <form action="{{ route('admin.member-groups.move', $g) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="dir" value="down">
                                    <button class="btn btn-sm btn-muted" type="submit">{{ admin_text('下移') }}</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.member-groups.toggle', $g) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-muted" type="submit">{{ $g->is_active ? admin_text('停用') : admin_text('启用') }}</button>
                            </form>
                            <form action="{{ route('admin.member-groups.destroy', $g) }}" method="post" onsubmit="return confirm(@js(($g->members_count ?? 0) > 0 ? admin_text('「:name」还有 :count 人。删掉后他们变成未分组，确定？', ['name' => $g->displayName(), 'count' => $g->members_count]) : admin_text('确定删除「:name」？', ['name' => $g->displayName() ?: $g->slug])))">
                                @csrf @method('DELETE')
                                <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 组', ['count' => $groups->count()]) }}</p>
        @endif
    </div>
@endsection
