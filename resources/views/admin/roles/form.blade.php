@extends('admin.layout')
@section('title', $role->exists ? admin_text('编辑角色') : admin_text('新建角色'))
@section('icon', 'user-shield')
@php
    $selected = old('permissions', $selected ?? []);
    if (! is_array($selected)) {
        $selected = [];
    }
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.roles.index') }}">{{ admin_back('roles') }}</a>
@endsection
@section('content')
    @if($role->exists && ($role->users_count ?? 0) > 0)
        <p class="muted recycle-lead">
            {{ admin_text('有') }}
            @if(auth('admin')->user()?->canPerm('admin_users'))
                <a href="{{ route('admin.admin-users.index', ['role' => $role->id]) }}">{{ admin_text(':count 人', ['count' => $role->users_count]) }}</a>
            @else
                {{ admin_text(':count 人', ['count' => $role->users_count]) }}
            @endif
            {{ admin_text('在用这个角色。改权限后，他们下次打开后台就会按新的来。') }}
        </p>
    @endif
    <form method="post" class="role-form" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
        @csrf
        @if($role->exists) @method('PUT') @endif
        <input type="hidden" name="from_form" value="1">

        <label for="role-name">{{ admin_text('名称') }}</label>
        <input id="role-name" type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="{{ admin_text('如 审核员') }}" required autofocus>
        <p class="muted field-hint">{{ admin_text('给管理员分角色时看到的名字。') }}</p>

        @if($role->exists)
            <p class="muted field-hint">{{ admin_text('标识码 :slug，建好后不用改。', ['slug' => $role->slug]) }}</p>
        @else
            <label for="role-slug">{{ admin_text('标识码') }}</label>
            <input id="role-slug" type="text" name="slug" value="{{ old('slug', $role->slug) }}" placeholder="{{ admin_text('如 reviewer') }}">
            <p class="muted field-hint">{{ admin_text('给程序用。可空时按名称生成。') }}</p>
        @endif

        <label for="role-remark">{{ admin_text('备注') }}</label>
        <input id="role-remark" type="text" name="remark" value="{{ old('remark', $role->remark) }}" placeholder="{{ admin_text('给自己看，可空') }}">

        @if($role->exists && $role->is_super)
            <p class="muted field-hint">{{ admin_text('这是超级管理员角色，能进所有菜单，不能改成普通角色。') }}</p>
        @else
            <p class="role-perms-label">{{ admin_text('可用功能与操作权限') }}</p>
            <p class="muted field-hint">{{ admin_text('决定可用功能及发布等操作能力。具体能改哪些栏目，请到管理员账号中设置。') }}</p>
            @foreach($permissionGroups as $group => $perms)
                <div class="role-perm-group">
                    <h3>{{ match ($group) { '概览' => admin_t('nav.overview'), '内容' => admin_t('nav.content'), '运营' => admin_t('nav.ops'), admin_text('系统') => admin_t('nav.system'), default => $group } }}</h3>
                    <div class="role-perms">
                        @foreach($perms as $perm)
                            <label class="inline">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->key }}"
                                    @checked(in_array($perm->key, $selected, true))>
                                {{ $perm->displayName() }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        <div class="form-actions">
            <button class="btn" type="submit">{{ $role->exists ? admin_text('保存') : admin_text('添加角色') }}</button>
            <a class="btn btn-muted" href="{{ route('admin.roles.index') }}">{{ admin_text('取消') }}</a>
        </div>
    </form>
@endsection
