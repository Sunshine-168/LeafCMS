@extends('admin.layout')
@section('title', admin_t('nav.menus'))
@section('icon', 'bars')
@section('header_actions')
    <a class="btn btn-sm" href="{{ route('admin.menus.create') }}"><i class="fas fa-plus"></i> {{ admin_text('新建菜单') }}</a>
@endsection
@section('content')
    <div class="entry-index menu-index">
        @if($menus->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有导航菜单。') }}</p>
                <p class="muted">{{ admin_text('页头、页脚这些位置各建一组。没有启用的菜单时，前台会改用栏目导航。') }}</p>
                <a class="btn" href="{{ route('admin.menus.create') }}">{{ admin_text('新建菜单') }}</a>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('点名称去改条目。位置要和主题里的调用一致，例如 @@cmsMenu([\'location\' => \'header\'])。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('菜单') }}</th>
                    <th style="width:120px">{{ admin_text('条目') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($menus as $menu)
                    @php $locLabel = \App\Models\Cms\Menu::locationLabel($menu->location); @endphp
                    <tr @class(['is-off' => ! $menu->is_active])>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.menus.edit', $menu) }}">{{ $menu->name }}</a>
                                @unless($menu->is_active)
                                    <span class="badge badge-off">{{ admin_text('停用') }}</span>
                                @endunless
                            </div>
                            <div class="entry-row-meta">
                                {{ $locLabel }}
                                @if($locLabel !== $menu->location)
                                    · {{ $menu->location }}
                                @endif
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.menus.edit', $menu) }}">{{ $menu->listingCount() }}</a>
                        </td>
                        <td class="actions">
                            <form action="{{ route('admin.menus.toggle', $menu) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-muted" type="submit">{{ $menu->is_active ? admin_text('停用') : admin_text('启用') }}</button>
                            </form>
                            <form action="{{ route('admin.menus.destroy', $menu) }}" method="post" onsubmit="return confirm(@js($menu->items->count() > 0 ? admin_text('确定删除「:name」？其中 :count 个条目也会删掉。前台没有这组菜单时会改用栏目导航。', ['name' => $menu->name, 'count' => $menu->items->count()]) : admin_text('确定删除「:name」？', ['name' => $menu->name])))">
                                @csrf @method('DELETE')
                                <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 组', ['count' => $menus->count()]) }}</p>
        @endif
    </div>
@endsection
