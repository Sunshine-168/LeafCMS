@extends('admin.layout')
@section('title', $member->exists ? admin_text('编辑会员') : admin_text('新建会员'))
@section('icon', 'users')
@php
    $posts = (int) ($member->contributed_entries_count ?? 0);
    $comments = (int) ($member->comments_count ?? 0);
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.members.index') }}">{{ admin_back('members') }}</a>
@endsection
@section('content')
    @if($member->exists && ($posts > 0 || $comments > 0))
        <p class="muted recycle-lead">
            @if($posts > 0 && $comments > 0)
                {{ admin_text('有 :posts 篇投稿，留过 :comments 条评论。改资料或停用不会删掉这些内容。', ['posts' => $posts, 'comments' => $comments]) }}
            @elseif($posts > 0)
                {{ admin_text('有 :posts 篇投稿。改资料或停用不会删掉这些内容。', ['posts' => $posts]) }}
            @else
                {{ admin_text('留过 :comments 条评论。改资料或停用不会删掉这些内容。', ['comments' => $comments]) }}
            @endif
        </p>
    @endif
    <form method="post" class="member-form" action="{{ $member->exists ? route('admin.members.update', $member) : route('admin.members.store') }}">
        @csrf
        @if($member->exists) @method('PUT') @endif

        <label for="member-name">{{ admin_text('名称') }}</label>
        <input id="member-name" type="text" name="name" value="{{ old('name', $member->name) }}" placeholder="{{ admin_text('如 张三') }}" required autofocus>
        <p class="muted field-hint">{{ admin_text('前台个人中心和评论里看到的名字。') }}</p>

        <label for="member-email">{{ admin_text('登录邮箱') }}</label>
        <input id="member-email" type="email" name="email" value="{{ old('email', $member->email) }}" required>
        <p class="muted field-hint">{{ admin_text('用来登录网站，不是后台账号。') }}</p>

        <label for="member-group">{{ admin_text('分组') }}</label>
        <select id="member-group" name="group_id">
            <option value="">{{ admin_text('未分组') }}</option>
            @foreach($groups ?? [] as $g)
                <option value="{{ $g->id }}" @selected(old('group_id', $member->group_id) == $g->id)>
                    {{ $g->displayName() }}{{ $g->is_active ? '' : ' '.admin_text('（已停用）') }}
                </option>
            @endforeach
        </select>
        <p class="muted field-hint">{!! str_replace(':groups', '<a href="'.e(route('admin.member-groups.index')).'">'.e(admin_text('分组')).'</a>', e(admin_text('决定能不能投稿、下载、留言。分组权限在 :groups 里改。'))) !!}</p>

        <label for="member-password">{{ admin_text('密码') }}{{ $member->exists ? admin_text('（留空不改）') : '' }}</label>
        <input id="member-password" type="password" name="password" autocomplete="new-password" {{ $member->exists ? '' : 'required' }} minlength="8">
        <p class="muted field-hint">{{ $member->exists ? admin_text('只有要帮他重设密码时才填。') : admin_text('至少 8 位，交给会员自己登录。') }}</p>

        <label for="member-phone">{{ admin_text('手机') }}</label>
        <input id="member-phone" type="text" name="phone" value="{{ old('phone', $member->phone) }}" maxlength="32" placeholder="{{ admin_text('可空') }}">

        <label for="member-bio">{{ admin_text('简介') }}</label>
        <textarea id="member-bio" name="bio" rows="3" maxlength="500" placeholder="{{ admin_text('可空') }}">{{ old('bio', $member->bio) }}</textarea>

        <label class="inline">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $member->is_active ?? true))>
            {{ admin_text('启用，可以登录前台') }}
        </label>

        <div class="form-actions">
            <button class="btn" type="submit">{{ $member->exists ? admin_text('保存') : admin_text('添加会员') }}</button>
            <a class="btn btn-muted" href="{{ route('admin.members.index') }}">{{ admin_text('取消') }}</a>
        </div>
    </form>
@endsection
