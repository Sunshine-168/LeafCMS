@extends('admin.layout')
@section('title', $user->exists ? admin_text('编辑管理员') : admin_text('新建管理员'))
@section('icon', 'user-cog')
@php
    $currentId = (int) ($currentId ?? 0);
    $superCount = (int) ($superCount ?? 0);
    $isSelf = $user->exists && $currentId === (int) $user->id;
    $isLastSuper = $user->exists && $user->is_active && $user->isSuperAdmin() && $superCount <= 1;
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.admin-users.index') }}">{{ admin_back('admins') }}</a>
@endsection
@section('content')
    @if($user->exists)
        <div class="card card-panel">
            <div class="card-header">{{ admin_text('当前已保存的权限') }}</div>
            <div class="card-body">
                <p>{{ admin_text('角色：') }}{{ $user->adminRole?->displayName() ?: admin_text('未分配角色') }} · {{ admin_text('账号：') }}{{ $user->is_active ? admin_text('已启用') : admin_text('已停用，不能登录') }}</p>
                <p>{{ admin_text('栏目范围：') }}{{ $user->allowedCategoryIds() === null ? admin_text('全部栏目') : ($categories->whereIn('id', $user->allowedCategoryIds())->map(fn ($cat) => $cat->t('title'))->join('、') ?: admin_text('没有匹配的栏目')) }}</p>
                <p>{{ admin_text('内容编辑：') }}{{ $user->canPerm('entries') ? admin_text('允许') : admin_text('不允许') }} · {{ admin_text('发布新内容：') }}{{ $user->canPerm('entries') && $user->canPerm('publish') ? admin_text('允许（仍受栏目范围限制）') : admin_text('不允许') }}</p>
                <p class="muted">{{ admin_text('角色决定操作能力，栏目决定管理范围。下方修改保存后才生效。') }}</p>
            </div>
        </div>
    @endif
    <form method="post" class="admin-user-form" action="{{ $user->exists ? route('admin.admin-users.update', $user) : route('admin.admin-users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif
        <input type="hidden" name="from_form" value="1">
        <input type="hidden" name="_has_category_scope" value="1">

        <label for="admin-user-name">{{ admin_text('名称') }}</label>
        <input id="admin-user-name" type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="{{ admin_text('如 小王') }}" required autofocus>
        <p class="muted field-hint">{{ admin_text('后台列表和顶栏看到的名字。') }}</p>

        <label for="admin-user-email">{{ admin_text('登录邮箱') }}</label>
        <input id="admin-user-email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
        <p class="muted field-hint">{{ admin_text('用来登录后台，和前台会员不是同一套账号。') }}</p>

        <label for="admin-user-password">{{ admin_text('登录密码') }}</label>
        <input id="admin-user-password" type="password" name="password" {{ $user->exists ? '' : 'required' }} minlength="8" autocomplete="new-password">
        <p class="muted field-hint">{{ $user->exists ? admin_text('留空则不改密码。') : '' }}{{ admin_text('至少 8 位。') }}</p>

        <label for="admin-user-role">{{ admin_text('角色') }}</label>
        @if($isLastSuper)
            <input type="hidden" name="role_id" value="{{ $user->role_id }}">
            <p class="muted field-hint">{{ admin_text('这是最后一位超级管理员，角色不能改。需要先再添一位超级管理员。') }}</p>
        @else
            <select id="admin-user-role" name="role_id" required>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                        {{ $role->displayName() }}{{ $role->is_super ? admin_text('（能做所有事）') : '' }}
                    </option>
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_text('决定可用功能和发布能力。要调整角色，去「角色与权限」。') }}</p>
        @endif

        @if($isSelf || $isLastSuper)
            <input type="hidden" name="is_active" value="1">
            <p class="muted field-hint">{{ $isSelf ? admin_text('这是你正在用的账号，不能停用。') : admin_text('最后一位超级管理员不能停用。') }}</p>
        @else
            <label class="inline">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>{{ admin_text('启用，可以登录后台') }}</label>
        @endif

        <h3>{{ admin_text('能改哪些栏目') }}</h3>
        <p class="muted field-hint">{{ admin_text('不勾表示这个站的栏目都能改。勾了就只能改勾上的。') }}</p>
        <div class="admin-user-checks">
            @forelse($categories as $cat)
                <label class="inline">
                    <input type="checkbox" name="allowed_category_ids[]" value="{{ $cat->id }}"
                        @checked(in_array($cat->id, old('allowed_category_ids', $user->allowed_category_ids ?? []), false))>
                    {{ $cat->t('title') }}
                </label>
            @empty
                <p class="muted">{{ admin_text('还没有栏目。') }}</p>
            @endforelse
        </div>

        <div class="form-actions">
            <button class="btn" type="submit">{{ $user->exists ? admin_text('保存') : admin_text('添加管理员') }}</button>
            <a class="btn btn-muted" href="{{ route('admin.admin-users.index') }}">{{ admin_text('取消') }}</a>
        </div>
    </form>
@endsection
