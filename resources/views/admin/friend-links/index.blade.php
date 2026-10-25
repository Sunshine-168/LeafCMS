@extends('admin.layout')
@section('title', admin_t('nav.friend_links'))
@section('icon', 'link')
@php
    $composeFocus = session('friend_link_quick_added') || $links->isEmpty();
@endphp
@section('content')
    <div class="entry-index friend-link-index">
        <div class="redirect-compose">
            <form method="post" action="{{ route('admin.friend-links.store') }}" class="redirect-compose-form">
                @csrf
                <input type="hidden" name="is_active" value="1">
                <label class="redirect-compose-label" for="friend-quick-title">{{ admin_text('添加友链') }}</label>
                <div class="redirect-compose-row">
                    <input id="friend-quick-title" type="text" name="title" value="" placeholder="{{ admin_text('网站名称，如 示例站') }}" aria-label="{{ admin_text('网站名称') }}" required @if($composeFocus) autofocus @endif>
                    <input type="text" name="url" value="" placeholder="{{ admin_text('网址，如 https://example.com') }}" aria-label="{{ admin_text('网址') }}" required>
                    <button class="btn" type="submit">{{ admin_text('添加') }}</button>
                </div>
                <p class="muted field-hint">{!! str_replace(':form', '<a href="'.e(route('admin.friend-links.create')).'">'.e(admin_text('打开完整表单')).'</a>', e(admin_text('这里是首页友情链接 / 合作伙伴，不是导航站那种网址目录。需要加 Logo 或改英文名时，:form。'))) !!}</p>
            </form>
        </div>

        @if($links->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有友链。') }}</p>
                <p class="muted">{!! str_replace(':name', '<code>'.e($block->name).'</code>', e(admin_text('填名称和网址即可添加。企业主题首页会调用推荐位 :name。'))) !!}</p>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('点名称去改 Logo 或其他语言。停用后前台不再显示，记录还留着。') }}@unless($block->is_active) {{ admin_text('整组推荐位目前是停用的，前台不会出这一栏。') }}@endunless</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('网站') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($links as $row)
                    <tr @class(['is-off' => ! $row->is_active])>
                        <td>
                            <div class="entry-row-title-line">
                                @if($row->image)
                                    <img src="{{ $row->image }}" alt="" class="friend-link-logo">
                                @endif
                                <a class="entry-row-title" href="{{ route('admin.friend-links.edit', $row) }}">{{ $row->t('title') ?: admin_text('未命名') }}</a>
                                @unless($row->is_active)
                                    <span class="badge badge-off">{{ admin_text('停用') }}</span>
                                @endunless
                            </div>
                            <div class="entry-row-meta">{{ $row->url }}</div>
                        </td>
                        <td class="actions">
                            <form action="{{ route('admin.friend-links.toggle', $row) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-muted" type="submit">{{ $row->is_active ? admin_text('停用') : admin_text('启用') }}</button>
                            </form>
                            <form action="{{ route('admin.friend-links.destroy', $row) }}" method="post" onsubmit="return confirm(@js(admin_text('确定删除「:name」？', ['name' => $row->t('title') ?: admin_text('这条友链')])))">
                                @csrf @method('DELETE')
                                <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 条友链', ['count' => $links->count()]) }}</p>
        @endif
    </div>
@endsection
