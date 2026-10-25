@extends('admin.layout')
@section('title', admin_t('nav.recommends'))
@section('icon', 'th-large')
@section('header_actions')
    <a class="btn btn-sm" href="{{ route('admin.recommends.create') }}"><i class="fas fa-plus"></i> {{ admin_text('新建推荐位') }}</a>
@endsection
@section('content')
    <div class="entry-index rec-index">
        @if($recommends->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有推荐位。') }}</p>
                <p class="muted">{{ admin_text('推荐位是首页轮播、合作伙伴这类前台区块。建好后主题用标识码调用。') }}</p>
                <a class="btn" href="{{ route('admin.recommends.create') }}">{{ admin_text('新建推荐位') }}</a>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('点名称去改条目或取数规则。标识码给主题用，例如 @@cmsAdv([\'name\' => \'home_banner\'])。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('推荐位') }}</th>
                    <th style="width:110px">{{ admin_text('类型') }}</th>
                    <th style="width:140px">{{ admin_text('内容') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($recommends as $recommend)
                    @php $label = $recommend->displayName(); @endphp
                    <tr @class(['is-off' => ! $recommend->is_active])>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.recommends.edit', $recommend) }}">{{ $label }}</a>
                                @unless($recommend->is_active)
                                    <span class="badge badge-off">{{ admin_text('停用') }}</span>
                                @endunless
                            </div>
                            <div class="entry-row-meta">{{ $recommend->name }}</div>
                        </td>
                        <td><span class="badge">{{ admin_text(\App\Models\Cms\Recommend::typeLabel($recommend->type)) }}</span></td>
                        <td>{{ $recommend->listingCount() }}</td>
                        <td class="actions">
                            <form action="{{ route('admin.recommends.toggle', $recommend) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-muted" type="submit">{{ $recommend->is_active ? admin_text('停用') : admin_text('启用') }}</button>
                            </form>
                            <form action="{{ route('admin.recommends.destroy', $recommend) }}" method="post" onsubmit="return confirm(@js($recommend->type === 'query' ? admin_text('确定删除「:name」？主题将取不到这个区块。', ['name' => $label]) : admin_text('确定删除「:name」？', ['name' => $label]).(($recommend->items_count ?? 0) > 0 ? admin_text('其中 :count 条手工条目也会删掉。', ['count' => $recommend->items_count]) : '')))">
                                @csrf @method('DELETE')
                                <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 个', ['count' => $recommends->count()]) }}</p>
        @endif
    </div>
@endsection
