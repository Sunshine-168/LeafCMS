@extends('admin.layout')
@section('title', admin_t('nav.collectors'))
@section('icon', 'cloud-download-alt')
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.collectors.logs') }}">{{ admin_t('crud.collector_logs') }}</a>
    <a class="btn btn-sm" href="{{ route('admin.collectors.create') }}"><i class="fas fa-plus"></i> {{ admin_text('新建采集源') }}</a>
@endsection
@section('content')
    <div class="entry-index collector-index">
        @if($collectors->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有采集任务。') }}</p>
                <p class="muted">{{ admin_text('从网站列表页、RSS 或接口把文章拉进栏目。先试抓，确认标题对了再启用。') }}</p>
                <a class="btn" href="{{ route('admin.collectors.create') }}">{{ admin_text('新建采集源') }}</a>
            </div>
        @else
            <p class="muted recycle-lead">
                {{ admin_text('点名称去改怎么抓。网站页面可翻页、进详情取正文；只抓你有权使用的公开内容。') }}
                @if($collectMode === 'off')
                    {{ admin_text('当前不会自动跑，只能点立即采集。') }}
                @elseif($collectMode === 'queue')
                    {{ admin_text('立即采集会进后台队列。') }}
                @else
                    {{ admin_text('到点会自动跑，也可以立刻采集。') }}
                @endif
            </p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('任务') }}</th>
                    <th style="width:160px">{{ admin_text('写入') }}</th>
                    <th style="width:140px">{{ admin_text('上次') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($collectors as $c)
                    <tr @class(['is-off' => ! $c->is_active])>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.collectors.edit', $c) }}">{{ $c->name ?: admin_text('未命名') }}</a>
                                <span class="badge">{{ admin_text(\App\Models\Cms\Collector::typeLabel($c->type)) }}</span>
                                @unless($c->is_active)
                                    <span class="badge badge-off">{{ admin_text('停用') }}</span>
                                @endunless
                                @if($c->is_active && ($c->last_status ?? '') === 'fail')
                                    <span class="badge badge-warn">{{ admin_text('上次失败') }}</span>
                                @endif
                            </div>
                            <div class="entry-row-meta">
                                {{ admin_text('每 :minutes 分钟 · 每次最多 :limit 条', ['minutes' => $c->interval_minutes, 'limit' => $c->limit_items]) }}
                            </div>
                        </td>
                        <td>
                            {{ $c->modelDef?->displayName() ?: '—' }}
                            @if($c->category)
                                / {{ $c->category->t('title') }}
                            @endif
                        </td>
                        <td class="muted">
                            @if($c->last_run_at)
                                {{ $c->last_run_at->format('m-d H:i') }}
                                @if($c->last_message)
                                    <div class="entry-row-meta">{{ \Illuminate\Support\Str::limit($c->last_message, 36) }}</div>
                                @endif
                            @else
                                {{ admin_text('还没跑过') }}
                            @endif
                        </td>
                        <td class="actions">
                            <form action="{{ route('admin.collectors.run', $c) }}" method="post">
                                @csrf
                                <button class="btn btn-sm" type="submit">{{ admin_text('立即采集') }}</button>
                            </form>
                            <form action="{{ route('admin.collectors.toggle', $c) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-muted" type="submit">{{ $c->is_active ? admin_text('停用') : admin_text('启用') }}</button>
                            </form>
                            <form action="{{ route('admin.collectors.destroy', $c) }}" method="post" onsubmit="return confirm(@js(admin_text('确定删除「:name」？已经采进来的内容还在。', ['name' => $c->name ?: admin_text('未命名')])))">
                                @csrf @method('DELETE')
                                <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 个', ['count' => $collectors->total()]) }}</p>
            {{ $collectors->links() }}
        @endif
    </div>
@endsection
