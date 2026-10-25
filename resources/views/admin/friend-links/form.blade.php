@extends('admin.layout')
@section('title', $link->exists ? admin_text('编辑友链') : admin_text('新建友链'))
@section('icon', 'link')
@php
    $default = \App\Support\Locale::default();
    $localeMap = \App\Support\Locale::all();
    $otherCodes = array_values(array_filter(\App\Support\Locale::codes(), fn ($c) => $c !== $default));
    $titleVal = old("locales.$default.title", $link->translation('title', $default) ?: old('title', ''));
    $imageVal = old('image', $link->image);
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.friend-links.index') }}">{{ admin_back('friend_links') }}</a>
@endsection
@section('content')
    <form method="post" class="friend-link-form" action="{{ $link->exists ? route('admin.friend-links.update', $link) : route('admin.friend-links.store') }}">
        @csrf
        @if($link->exists) @method('PUT') @endif
        <input type="hidden" name="from_form" value="1">

        <label for="friend-title">{{ admin_text('网站名称') }}</label>
        <input id="friend-title" type="text" name="locales[{{ $default }}][title]" value="{{ $titleVal }}" placeholder="{{ admin_text('如 示例站') }}" required autofocus>
        <p class="muted field-hint">{{ admin_text('前台友情链接上显示的名字。') }}</p>

        <label for="friend-url">{{ admin_text('网址') }}</label>
        <input id="friend-url" type="text" name="url" value="{{ old('url', $link->url) }}" placeholder="https://example.com" required>
        <p class="muted field-hint">{{ admin_text('一般填完整 https://…。没写协议时会自动加上 https://。') }}</p>

        <label>Logo</label>
        <div class="media-field" data-media-preview="#friendLogo">
            <div class="media-preview" @if(! $imageVal) hidden @endif>
                <img src="{{ $imageVal }}" alt="{{ admin_text('Logo 预览') }}">
                <button type="button" class="media-preview-clear" title="{{ admin_text('移除 Logo') }}">&times;</button>
            </div>
            <div class="cover-row">
                <input type="text" id="friendLogo" name="image" value="{{ $imageVal }}" placeholder="{{ admin_text('可空，图片地址') }}">
                <button type="button" class="btn btn-muted" data-media-pick="#friendLogo">{{ admin_text('选择') }}</button>
            </div>
        </div>
        <p class="muted field-hint">{{ admin_text('没有 Logo 时前台只显示名称。') }}</p>

        @if($otherCodes !== [])
            <details class="entry-seo" @if($link->exists) open @endif>
                <summary>{{ admin_text('其他语言') }}</summary>
                @foreach($otherCodes as $code)
                    <label>{{ admin_text(':lang 名称', ['lang' => $localeMap[$code]['label'] ?? $code]) }}</label>
                    <input type="text" name="locales[{{ $code }}][title]" value="{{ old("locales.$code.title", $link->translation('title', $code)) }}" placeholder="{{ admin_text('可空，默认用上面的名称') }}">
                @endforeach
            </details>
        @endif

        <label for="friend-sort">{{ admin_text('排序') }}</label>
        <input id="friend-sort" type="number" name="sort_order" value="{{ old('sort_order', $link->sort_order ?? 0) }}" min="0">
        <p class="muted field-hint">{{ admin_text('数字越小越靠前。') }}</p>

        <label class="inline">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $link->is_active ?? true))>
            {{ admin_text('启用，前台显示') }}
        </label>

        <div class="form-actions">
            <button class="btn" type="submit">{{ $link->exists ? admin_text('保存') : admin_text('添加友链') }}</button>
            <a class="btn btn-muted" href="{{ route('admin.friend-links.index') }}">{{ admin_text('取消') }}</a>
        </div>
    </form>
    @include('admin.partials.editor-assets')
@endsection
