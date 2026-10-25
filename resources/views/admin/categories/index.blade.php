@extends('admin.layout')
@section('title', admin_t('nav.categories'))
@section('icon', 'sitemap')
@section('header_actions')
    <a class="btn btn-sm" href="{{ route('admin.categories.create') }}"><i class="fas fa-plus"></i> {{ admin_text('新建栏目') }}</a>
@endsection
@section('content')
    <div class="entry-index cat-index">
        @if($categories->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有栏目。') }}</p>
                <p class="muted">{{ admin_text('栏目是内容的目录。先建一级栏目，再在它下面「添加下级」做多级频道。') }}</p>
                <a class="btn" href="{{ route('admin.categories.create') }}">{{ admin_text('新建栏目') }}</a>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('下级会缩进显示。点「添加下级」挂到这一栏下面；删除前请先移走子栏目和内容。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('栏目') }}</th>
                    <th style="width:88px">{{ admin_text('类型') }}</th>
                    <th style="width:110px">{{ admin_text('模型') }}</th>
                    <th style="width:88px">{{ admin_text('导航栏') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($categories as $category)
                    @php $depth = (int) ($category->depth ?? 0); @endphp
                    <tr>
                        <td>
                            <div class="cat-cell" style="padding-left: {{ $depth * 22 }}px">
                                @if($depth > 0)
                                    <span class="cat-branch" aria-hidden="true">└</span>
                                @endif
                                <div>
                                    <a class="entry-row-title" href="{{ route('admin.categories.edit', $category) }}">{{ $category->displayTitle() ?: admin_text('无标题') }}</a>
                                    <div class="entry-row-meta">
                                        /{{ $category->t('slug') }}
                                        · #{{ $category->id }}
                                        @if(($category->entries_count ?? 0) > 0)
                                            · <a href="{{ route('admin.entries.index', array_filter(['model_id' => $category->model_id, 'category_id' => $category->id])) }}">{{ admin_text(':count 篇', ['count' => $category->entries_count]) }}</a>
                                        @endif
                                        @if($category->status !== 'published')
                                            · {{ admin_text('草稿') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge">{{ admin_text(\App\Models\Cms\Category::typeLabel($category->type)) }}</span></td>
                        <td>{{ $category->modelDef?->displayName() ?: '—' }}</td>
                        <td>
                            @if($category->is_menu)
                                <span class="badge badge-search">{{ admin_text('导航栏') }}</span>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td class="actions">
                            @if($category->status === 'published')
                                <a class="btn btn-sm btn-muted" href="{{ url($category->frontUrl()) }}" target="_blank" rel="noopener">{{ admin_text('前台') }}</a>
                            @endif
                            <a class="btn btn-sm btn-muted" href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}">{{ admin_text('添加下级') }}</a>
                            <a class="btn btn-sm btn-muted" href="{{ route('admin.categories.edit', $category) }}">{{ admin_text('编辑') }}</a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="post" onsubmit="return confirm(@js(admin_text('删除「:name」？有下级或内容时无法删除。', ['name' => $category->displayTitle() ?: admin_text('无标题')])))">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
