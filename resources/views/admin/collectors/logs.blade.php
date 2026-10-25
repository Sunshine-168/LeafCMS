@extends('admin.layout')
@section('title', admin_t('crud.collector_logs'))
@section('icon', 'history')
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.collectors.index') }}">{{ admin_back('collectors') }}</a>
@endsection
@section('content')
    <div class="entry-index collector-logs">
        @if($logs->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有运行记录。') }}</p>
                <p class="muted">{{ admin_text('保存采集源后点立即采集，这里会记下每次拉了多少、新建了多少。') }}</p>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('最近的采集结果。失败时看说明，改选择器后再试。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('采集源') }}</th>
                    <th style="width:90px">{{ admin_text('结果') }}</th>
                    <th>{{ admin_text('说明') }}</th>
                    <th style="width:120px">{{ admin_text('时间') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>
                            @if($log->collector)
                                <a class="entry-row-title" href="{{ route('admin.collectors.edit', $log->collector) }}">{{ $log->collector->name }}</a>
                            @else
                                <span class="muted">{{ admin_text('已删除') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($log->status === 'ok')
                                <span class="badge badge-ok">{{ admin_text('成功') }}</span>
                            @elseif($log->status === 'fail')
                                <span class="badge badge-warn">{{ admin_text('失败') }}</span>
                            @else
                                <span class="badge">{{ $log->status }}</span>
                            @endif
                        </td>
                        <td class="muted">{{ $log->message }}</td>
                        <td class="muted">{{ $log->created_at?->format('m-d H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $logs->links() }}
        @endif
    </div>
@endsection
