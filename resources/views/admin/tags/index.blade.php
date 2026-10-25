@extends('admin.layout')
@section('title', admin_t('nav.tags'))
@section('icon', 'tags')
@php
    $q = $q ?? '';
    $unused = (bool) ($unused ?? false);
    $hasFilters = $q !== '' || $unused;
    $qs = function (array $extra = []) use ($q, $unused) {
        return route('admin.tags.index', array_filter(
            array_merge(['q' => $q, 'unused' => $unused ? '1' : null], $extra),
            fn ($v) => $v !== '' && $v !== null
        ));
    };
    $composeFocus = session('tag_quick_added') || ($tags->isEmpty() && ! $hasFilters);
@endphp
@section('content')
    <div class="entry-index tag-index">
        <div class="tag-compose">
            <form method="post" action="{{ route('admin.tags.store') }}" class="tag-compose-form">
                @csrf
                <input type="hidden" name="keep_q" value="{{ $q }}">
                <input type="hidden" name="keep_unused" value="{{ $unused ? '1' : '' }}">
                <label class="tag-compose-label" for="tag-quick-title">{{ admin_text('新增标签') }}</label>
                <div class="tag-compose-row">
                    <input id="tag-quick-title" type="text" name="title" value="" placeholder="{{ admin_text('输入名称，如 PHP') }}" aria-label="{{ admin_text('新增标签') }}" required @if($composeFocus) autofocus @endif>
                    <button class="btn" type="submit">{{ admin_text('添加') }}</button>
                </div>
                <p class="muted field-hint">{!! str_replace(':form', '<a href="'.e(route('admin.tags.create')).'">'.e(admin_text('打开完整表单')).'</a>', e(admin_text('回车可连续添加。需要改网址或英文名时，:form。'))) !!}</p>
            </form>
        </div>

        <form method="get" class="filter-bar tag-find" action="{{ route('admin.tags.index') }}">
            @if($unused)
                <input type="hidden" name="unused" value="1">
            @endif
            <input type="search" name="q" value="{{ $q }}" placeholder="{{ admin_text('搜索标签名或网址标识') }}" aria-label="{{ admin_text('搜索标签') }}">
            <button class="btn btn-muted" type="submit">{{ admin_text('搜索') }}</button>
            <a class="chip {{ ! $unused ? 'active' : '' }}" href="{{ $qs(['unused' => null]) }}">{{ admin_text('全部') }}</a>
            <a class="chip {{ $unused ? 'active' : '' }}" href="{{ $qs(['unused' => '1']) }}">{{ admin_text('未使用') }}</a>
            @if($hasFilters)
                <a class="chip" href="{{ route('admin.tags.index') }}">{{ admin_text('清除筛选') }}</a>
            @endif
        </form>

        @if($tags->isEmpty())
            <div class="list-empty">
                @if($hasFilters)
                    <p>{{ admin_text('没有符合条件的标签。') }}</p>
                    <a class="btn btn-muted" href="{{ route('admin.tags.index') }}">{{ admin_text('清除筛选') }}</a>
                @else
                    <p>{{ admin_text('还没有标签。') }}</p>
                    <p class="muted">{{ admin_text('在上方输入名称即可添加，用来给内容归类。') }}</p>
                @endif
            </div>
        @else
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('标签') }}</th>
                    <th style="width:120px">{{ admin_text('内容') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($tags as $tag)
                    <tr>
                        <td>
                            <a class="entry-row-title" href="{{ route('admin.tags.edit', $tag) }}">{{ $tag->t('title') ?: admin_text('无标题') }}</a>
                            <div class="entry-row-meta">/tag/{{ $tag->t('slug') }} · #{{ $tag->id }}</div>
                        </td>
                        <td>
                            @if($tag->entries_count > 0)
                                <a href="{{ route('admin.entries.index', ['tag_id' => $tag->id]) }}">{{ admin_text(':count 篇', ['count' => $tag->entries_count]) }}</a>
                            @else
                                <span class="muted">{{ admin_text('未使用') }}</span>
                            @endif
                        </td>
                        <td class="actions">
                            <a class="btn btn-sm btn-muted" href="{{ cms_front_url('tag/'.$tag->t('slug')) }}" target="_blank" rel="noopener">{{ admin_text('前台') }}</a>
                            <a class="btn btn-sm btn-muted" href="{{ route('admin.tags.edit', $tag) }}">{{ admin_text('编辑') }}</a>
                            <form action="{{ route('admin.tags.destroy', $tag) }}" method="post" onsubmit="return confirm(@js($tag->entries_count > 0 ? admin_text('「:name」用在 :count 篇内容上，删除后只去掉标签，稿件还在。确定？', ['name' => $tag->t('title'), 'count' => $tag->entries_count]) : admin_text('确定删除「:name」？', ['name' => $tag->t('title') ?: admin_text('无标题')])))">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 个', ['count' => $tags->total()]) }}</p>
            {{ $tags->links() }}
        @endif
    </div>
@endsection
