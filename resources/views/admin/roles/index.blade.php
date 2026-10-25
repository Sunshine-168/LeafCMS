@extends('admin.layout')
@section('title', admin_t('nav.roles'))
@section('icon', 'user-shield')
@php
    $composeFocus = session('role_quick_added') || $roles->isEmpty();
@endphp
@section('header_actions')
    @if(auth('admin')->user()?->canPerm('admin_users'))
        <a class="btn btn-muted btn-sm" href="{{ route('admin.admin-users.index') }}">{{ admin_text('管理员') }}</a>
    @endif
@endsection
@section('content')
    <div class="entry-index role-index">
        <div class="role-compose">
            <form method="post" action="{{ route('admin.roles.store') }}">
                @csrf
                <label class="role-compose-label" for="role-quick-name">{{ admin_text('新增角色') }}</label>
                <div class="role-compose-row">
                    <input id="role-quick-name" type="text" name="name" value="" placeholder="{{ admin_text('名称，如 审核员') }}" aria-label="{{ admin_text('角色名称') }}" required @if($composeFocus) autofocus @endif>
                    <button class="btn" type="submit">{{ admin_text('添加') }}</button>
                </div>
                <p class="muted field-hint">{{ admin_text('添加后点名称去勾能进哪些菜单。需要一次勾好时，') }}<a href="{{ route('admin.roles.create') }}">{{ admin_text('打开完整表单') }}</a>。</p>
            </form>
        </div>

        @if($roles->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有角色。') }}</p>
                <p class="muted">{{ admin_text('在上方填写名称即可添加。') }}</p>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('点名称去勾权限。超级管理员能做所有事，不能删。有人在用的角色，要先换人再删。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('角色') }}</th>
                    <th style="width:120px">{{ admin_text('管理员') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.roles.edit', $role) }}">{{ $role->displayName() ?: admin_text('未命名') }}</a>
                                @if($role->is_super)
                                    <span class="badge">{{ admin_text('能做所有事') }}</span>
                                @endif
                            </div>
                            <div class="entry-row-meta">
                                {{ $role->slug }}
                                @if($role->is_super)
                                    · {{ admin_text('不用勾菜单') }}
                                @else
                                    · {{ admin_text(':count 项菜单', ['count' => (int) $role->permissions_count]) }}
                                @endif
                                @if($role->displayRemark() !== '')
                                    · {{ $role->displayRemark() }}
                                @endif
                            </div>
                        </td>
                        <td>
                            @if(($role->users_count ?? 0) > 0)
                                @if(auth('admin')->user()?->canPerm('admin_users'))
                                    <a href="{{ route('admin.admin-users.index', ['role' => $role->id]) }}">{{ admin_text(':count 人', ['count' => $role->users_count]) }}</a>
                                @else
                                    {{ admin_text(':count 人', ['count' => $role->users_count]) }}
                                @endif
                            @else
                                <span class="muted">{{ admin_text('还没人') }}</span>
                            @endif
                        </td>
                        <td class="actions">
                            @unless($role->is_super)
                                @if(($role->users_count ?? 0) === 0)
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="post" onsubmit="return confirm(@js(admin_text('确定删除「:name」？', ['name' => $role->displayName() ?: $role->slug])))">
                                        @csrf @method('DELETE')
                                        <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                                    </form>
                                @endif
                            @endunless
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 个', ['count' => $roles->count()]) }}</p>
        @endif
    </div>
@endsection
