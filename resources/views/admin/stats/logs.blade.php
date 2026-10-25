@extends('admin.layout')
@section('title', admin_t('crud.stats_logs'))
@section('icon', 'list')
@section('fullwidth', true)
@section('content')
    @include('admin.stats._nav', ['active' => 'logs'])

    @php
        $today = now()->toDateString();
        $weekFrom = now()->subDays(6)->toDateString();
        $qs = fn (array $extra = []) => route('admin.stats.logs', array_filter(
            array_merge($filters, $extra),
            fn ($v) => $v !== '' && $v !== null
        ));
    @endphp

    <form method="get" class="filter-bar">
        @foreach(['bot', 'status', 'ip', 'hash', 'probe'] as $keep)
            @if($filters[$keep] !== '')
                <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
            @endif
        @endforeach
        <div class="field">
            <label for="log-from">{{ admin_text('从') }}</label>
            <input id="log-from" type="date" name="from" value="{{ $filters['from'] }}">
        </div>
        <div class="field">
            <label for="log-to">{{ admin_text('到') }}</label>
            <input id="log-to" type="date" name="to" value="{{ $filters['to'] }}">
        </div>
        <div class="field">
            <label for="log-visitor">{{ admin_text('类型') }}</label>
            <select id="log-visitor" name="visitor">
                <option value="" @selected($filters['visitor'] === '')>{{ admin_text('全部') }}</option>
                <option value="human" @selected($filters['visitor'] === 'human')>{{ admin_text('人类') }}</option>
                <option value="spider" @selected($filters['visitor'] === 'spider')>{{ admin_text('蜘蛛') }}</option>
            </select>
        </div>
        <div class="field">
            <label for="log-path">{{ admin_text('页面') }}</label>
            <input id="log-path" type="text" name="path" value="{{ $filters['path'] }}" placeholder="{{ admin_text('标题或路径，如 /news') }}">
        </div>
        <button class="btn" type="submit">{{ admin_text('筛选') }}</button>
        <a class="chip {{ $filters['from'] === $today && $filters['to'] === $today ? 'active' : '' }}" href="{{ $qs(['from' => $today, 'to' => $today]) }}">{{ admin_text('今天') }}</a>
        <a class="chip {{ $filters['from'] === $weekFrom && $filters['to'] === $today ? 'active' : '' }}" href="{{ $qs(['from' => $weekFrom, 'to' => $today]) }}">{{ admin_text('近 7 天') }}</a>
        <a class="chip {{ $filters['status'] === '404' ? 'active' : '' }}" href="{{ $qs(['status' => $filters['status'] === '404' ? '' : '404']) }}">{{ admin_text('仅 404') }}</a>
        <a class="chip {{ $filters['probe'] === 'hide' ? 'active' : '' }}" href="{{ $qs(['probe' => $filters['probe'] === 'hide' ? '' : 'hide']) }}">{{ admin_text('排除本机') }}</a>
        <a class="chip" href="{{ route('admin.stats.logs.export', $filters) }}">{{ admin_text('导出') }}</a>
        @if($activeFilters > 0)
            <a class="chip" href="{{ route('admin.stats.logs') }}">{{ admin_text('清除') }}</a>
        @endif
    </form>

    <p class="muted" style="margin:-4px 0 12px">
        {{ admin_text('共 :n 条', ['n' => $logs->total()]) }}
        @if($errorCount > 0)
            · {{ admin_text('非 200 :n 条', ['n' => $errorCount]) }}
        @endif
        @if($filters['ip'] !== '')
            · IP {{ $filters['ip'] }}
        @endif
        @if($filters['bot'] !== '')
            · {{ $filters['bot'] }}
        @endif
    </p>

    @if($trail->isNotEmpty())
        <div class="card card-panel" style="margin-bottom:16px">
            <div class="card-header">
                <span>{{ admin_text('该访客轨迹（:n 步）', ['n' => $trail->count()]) }}</span>
                <a class="btn btn-sm btn-muted" href="{{ $qs(['hash' => '']) }}">{{ admin_text('退出轨迹') }}</a>
            </div>
            <div class="card-body">
                <p class="trail">
                    @foreach($trail as $i => $step)
                        @if($i > 0)<span class="muted"> → </span>@endif
                        <span @class(['trail-err' => $step['status'] !== 200]) title="{{ $step['path'] }} · {{ $step['at'] }}">{{ $step['title'] }}</span>
                    @endforeach
                </p>
            </div>
        </div>
    @endif

    <div class="card card-panel">
        <div class="card-body" style="padding:0">
            <table class="data" style="border:0">
                <thead>
                <tr>
                    <th>{{ admin_text('时间') }}</th>
                    <th>{{ admin_text('访问者') }}</th>
                    <th>{{ admin_text('页面') }}</th>
                    <th>{{ admin_text('来路') }}</th>
                    <th>{{ admin_text('状态') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr class="{{ $row['status'] !== 200 ? 'log-row-error' : '' }}">
                        <td title="{{ $row['at'] }}">{{ optional($row['at'])->format('m-d H:i') }}</td>
                        <td>
                            <span class="badge {{ $row['visitor_kind'] === 'bot' ? 'badge-tool' : 'badge-search' }}">{{ $row['visitor'] }}</span>
                            @if($row['local'])
                                <span class="badge badge-off">{{ admin_text('本机') }}</span>
                            @endif
                            <div class="muted" title="{{ $row['ua'] }}">{{ $row['client'] }} · {{ $row['ip'] }}</div>
                        </td>
                        <td>
                            <a href="{{ $row['front_url'] }}" target="_blank" rel="noopener">{{ $row['title'] }}</a>
                            <div class="muted">{{ $row['path'] }}</div>
                        </td>
                        <td>{{ $row['referer'] }}</td>
                        <td>
                            @if($row['status'] !== 200)
                                <span class="badge badge-warn">{{ $row['status'] }}</span>
                            @else
                                200
                            @endif
                        </td>
                        <td class="log-actions">
                            @if($row['ip'])
                                <a href="{{ $qs(['ip' => $row['ip'], 'hash' => '']) }}">{{ admin_text('同 IP') }}</a>
                            @endif
                            @if($row['hash'])
                                <a href="{{ $qs(['hash' => $row['hash'], 'ip' => '']) }}">{{ admin_text('轨迹') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">{{ admin_text('该条件下没有访问记录') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $logs->links() }}
@endsection
