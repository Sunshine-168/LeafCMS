@extends('admin.layout')
@section('title', admin_t('nav.flags'))
@section('icon', 'flag')
@php
    $composeFocus = session('flag_quick_added') || $flags->isEmpty();
@endphp
@section('content')
    <div class="entry-index flag-index">
        <div class="flag-compose">
            <form method="post" action="{{ route('admin.flags.store') }}">
                @csrf
                <label class="flag-compose-label" for="flag-quick-title">{{ admin_text('新增推荐属性') }}</label>
                <div class="flag-compose-row">
                    <input id="flag-quick-title" type="text" name="title" value="" placeholder="{{ admin_text('显示名，如 置顶') }}" aria-label="{{ admin_text('显示名称') }}" required @if($composeFocus) autofocus @endif>
                    <input class="flag-key" type="text" name="name" value="" placeholder="{{ admin_text('标识码，如 top') }}" aria-label="{{ admin_text('标识码') }}">
                    <button class="btn" type="submit">{{ admin_text('添加') }}</button>
                </div>
                <p class="muted field-hint">{{ admin_text('会出现在写内容页。标识码给模板用，建好后尽量不要改。') }}</p>
            </form>
        </div>

        @if($flags->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有推荐属性。') }}</p>
                <p class="muted">{{ admin_text('在上方填写显示名和标识码即可添加。') }}</p>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('写内容时勾选这些属性，列表里会显示对应标记。停用后不再出现在勾选里，已打在稿上的还在。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('属性') }}</th>
                    <th style="width:120px">{{ admin_text('内容') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($flags as $i => $flag)
                    @php $label = $flag->displayName(); @endphp
                    <tr @class(['is-off' => ! $flag->is_active])>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.flags.edit', $flag) }}">{{ $label }}</a>
                                @unless($flag->is_active)
                                    <span class="badge badge-off">{{ admin_text('停用') }}</span>
                                @endunless
                            </div>
                            <div class="entry-row-meta">{{ $flag->name }}</div>
                        </td>
                        <td>
                            @if(($flag->entries_count ?? 0) > 0)
                                <a href="{{ route('admin.entries.index', ['flag' => $flag->name]) }}">{{ admin_text(':count 篇', ['count' => $flag->entries_count]) }}</a>
                            @else
                                <span class="muted">{{ admin_text('未使用') }}</span>
                            @endif
                        </td>
                        <td class="actions">
                            @if($i > 0)
                                <form action="{{ route('admin.flags.move', $flag) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="dir" value="up">
                                    <button class="btn btn-sm btn-muted" type="submit">{{ admin_text('上移') }}</button>
                                </form>
                            @endif
                            @if($i < $flags->count() - 1)
                                <form action="{{ route('admin.flags.move', $flag) }}" method="post">
                                    @csrf
                                    <input type="hidden" name="dir" value="down">
                                    <button class="btn btn-sm btn-muted" type="submit">{{ admin_text('下移') }}</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.flags.toggle', $flag) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-muted" type="submit">{{ $flag->is_active ? admin_text('停用') : admin_text('启用') }}</button>
                            </form>
                            <form action="{{ route('admin.flags.destroy', $flag) }}" method="post" onsubmit="return confirm(@js(($flag->entries_count ?? 0) > 0 ? admin_text('「:name」还打在 :count 篇上。删掉后写内容页不再列出，稿上的标记还在。确定？', ['name' => $label, 'count' => $flag->entries_count]) : admin_text('确定删除「:name」？', ['name' => $label])))">
                                @csrf @method('DELETE')
                                <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 项', ['count' => $flags->count()]) }}</p>
        @endif
    </div>
@endsection
