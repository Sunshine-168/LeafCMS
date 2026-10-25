@extends('admin.layout')
@section('title', $group->exists ? admin_text('编辑分组') : admin_text('新建分组'))
@section('icon', 'user-tag')
@php
    $usage = (int) ($usage ?? 0);
    $permLabels = \App\Models\Cms\MemberGroup::permissionLabels();
    $selected = old('permissions', $group->permissions);
    if (! is_array($selected) || $selected === []) {
        $selected = array_keys($permLabels);
    }
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.member-groups.index') }}">{{ admin_back('groups') }}</a>
@endsection
@section('content')
    @if($group->exists && $usage > 0)
        <p class="muted recycle-lead">{!! str_replace(':people', '<a href="'.e(route('admin.members.index', ['group' => $group->id])).'">'.e(admin_text(':count 人', ['count' => $usage])).'</a>', e(admin_text('有 :people 在这组。改名称不影响已分好的人；停用后他们暂时用不了这组权限。'))) !!}</p>
    @endif
    <form method="post" class="member-group-form" action="{{ $group->exists ? route('admin.member-groups.update', $group) : route('admin.member-groups.store') }}">
        @csrf
        @if($group->exists) @method('PUT') @endif
        <input type="hidden" name="from_form" value="1">

        <label for="group-name">{{ admin_text('显示名称') }}</label>
        <input id="group-name" type="text" name="name" value="{{ old('name', $group->name) }}" placeholder="{{ admin_text('如 普通会员') }}" required autofocus>
        <p class="muted field-hint">{{ admin_text('给会员分档时看到的名字。') }}</p>

        <label for="group-slug">{{ admin_text('标识码') }}</label>
        <input id="group-slug" type="text" name="slug" value="{{ old('slug', $group->slug) }}" placeholder="{{ admin_text('如 default') }}" >
        <p class="muted field-hint">{{ admin_text('给程序用。可空时按名称生成，建好后尽量不要改。') }}</p>

        <p class="member-group-perms-label">{{ admin_text('前台能做什么') }}</p>
        <div class="member-group-perms">
            @foreach($permLabels as $key => $label)
                <label class="inline">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked(in_array($key, $selected, true))>
                    {{ $label }}
                </label>
            @endforeach
        </div>
        <p class="muted field-hint">{{ admin_text('不勾的功能，这组成员在前台用不了。') }}</p>

        <label class="inline">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $group->is_active ?? true))>
            {{ admin_text('启用，这组权限生效') }}
        </label>

        <div class="form-actions">
            <button class="btn" type="submit">{{ $group->exists ? admin_text('保存') : admin_text('添加分组') }}</button>
            <a class="btn btn-muted" href="{{ route('admin.member-groups.index') }}">{{ admin_text('取消') }}</a>
        </div>
    </form>
@endsection
