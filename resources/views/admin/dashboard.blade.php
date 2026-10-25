@extends('admin.layout')
@section('title', admin_t('nav.dashboard'))
@section('fullwidth', true)
@section('content')
    @php
        $dashLocale = str_replace('-', '_', strtolower(app()->getLocale()));
        $dashCjkStop = str_starts_with($dashLocale, 'zh') || str_starts_with($dashLocale, 'ja');
        $deskDrafts = $desk['drafts'] ?? collect();
        $deskRecent = $desk['recent'] ?? collect();
        $deskScheduled = $desk['scheduled'] ?? collect();
        $showSpark = ($modules['traffic'] ?? false) && ($spark['pv'] ?? '') !== '';
        $showDesk = (bool) ($modules['content'] ?? false);
        $showTodos = (bool) ($modules['todos'] ?? false);
        $showActivity = (bool) ($modules['activity'] ?? false);
    @endphp
    @if($kpis !== [])
        <div class="dash-hero {{ $showSpark ? '' : 'solo' }}">
            <div class="stat-grid dash">
                @foreach($kpis as $card)
                    <a class="stat-card" href="{{ $card['href'] }}">
                        <div>
                            <div class="label">{{ $card['label'] }}</div>
                            <div class="value">{{ $card['value'] }}</div>
                            @if($card['delta'])
                                <div class="delta {{ $card['delta']['dir'] }}">{{ $card['delta']['text'] }}</div>
                            @endif
                            @if($card['hint'])
                                <div class="hint">{{ $card['hint'] }}</div>
                            @endif
                        </div>
                        <div class="icon {{ $card['color'] }}"><i class="fas {{ $card['icon'] }}"></i></div>
                    </a>
                @endforeach
            </div>
            @if($showSpark)
                <a class="card card-panel dash-spark" href="{{ route('admin.stats.index') }}">
                    <div class="card-header"><span>{{ admin_text('近 7 日 PV / UV') }}</span></div>
                    <div class="card-body">
                        <svg class="spark" viewBox="0 0 {{ $spark['width'] }} {{ $spark['height'] }}" preserveAspectRatio="none">
                            <polyline points="{{ $spark['pv'] }}" fill="none" stroke="#007bff" stroke-width="2"/>
                            <polyline points="{{ $spark['uv'] }}" fill="none" stroke="#28a745" stroke-width="2"/>
                        </svg>
                        <p class="muted spark-legend"><span class="dot blue"></span> PV <span class="dot green"></span> {{ admin_text('UV · 点进统计') }}</p>
                    </div>
                </a>
            @endif
        </div>
    @endif

    @if($showDesk || $showTodos || $showActivity)
        <div class="dash-work" id="dash-desk">
            @if($showDesk || $showTodos)
                <div class="dash-stack">
                    @if($showDesk)
                        <div class="card card-panel">
                            <div class="card-header"><span>{{ admin_text('我的草稿') }}</span></div>
                            @forelse($deskDrafts as $row)
                                <div class="todo-row"><div class="grow"><a href="{{ route('admin.entries.edit', $row) }}">{{ $row->uiTitle() ?: admin_text('未命名') }}</a></div></div>
                            @empty
                                <p class="muted dash-empty">{{ admin_text('还没有你保存的草稿。') }}</p>
                            @endforelse
                        </div>
                        <div class="card card-panel">
                            <div class="card-header"><span>{{ admin_text('即将发布') }}</span></div>
                            @forelse($deskScheduled as $row)
                                <div class="todo-row"><div class="grow"><a href="{{ route('admin.entries.edit', $row) }}">{{ $row->uiTitle() ?: admin_text('未命名') }}</a><div class="muted">{{ optional($row->published_at)->format('Y-m-d H:i') }}</div></div></div>
                            @empty
                                <p class="muted dash-empty">{{ admin_text('没有已定时、尚未到点的内容。') }}</p>
                            @endforelse
                        </div>
                    @endif
                    @if($showTodos)
                        <div class="card card-panel" id="dash-todos">
                            <div class="card-header">
                                <span>{{ admin_text('待我处理') }}</span>
                                @if(($todoCounts['total'] ?? 0) > 0)
                                    <span class="badge badge-warn">{{ $todoCounts['total'] }}</span>
                                @endif
                            </div>
                            @forelse($todos as $todo)
                                <div class="todo-row">
                                    <span class="badge {{ $todo['kind'] === 'entry' ? 'badge-warn' : ($todo['kind'] === 'form' ? 'badge-search' : 'badge-tool') }}">{{ $todo['kind_label'] }}</span>
                                    <div class="grow">
                                        <div>{{ $todo['title'] }}</div>
                                        <div class="muted">{{ optional($todo['time'])->diffForHumans() }}</div>
                                    </div>
                                    <a class="btn btn-sm" href="{{ $todo['url'] }}">{{ admin_text('去处理') }}</a>
                                </div>
                            @empty
                                <p class="muted dash-empty">{{ admin_text('当前没有待处理事项。') }}</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            @endif
            <div class="dash-stack">
                @if($showDesk)
                    <div class="card card-panel">
                        <div class="card-header"><span>{{ admin_text('最近编辑') }}</span></div>
                        @forelse($deskRecent as $row)
                            <div class="todo-row"><div class="grow"><a href="{{ route('admin.entries.edit', $row) }}">{{ $row->uiTitle() ?: admin_text('未命名') }}</a><div class="muted">{{ optional($row->updated_at)->diffForHumans() }}</div></div></div>
                        @empty
                            <p class="muted dash-empty">{{ admin_text('暂无数据') }}</p>
                        @endforelse
                    </div>
                @endif
                @if($showActivity)
                    <div class="card card-panel">
                        <div class="card-header"><span>{{ admin_text('最近动态') }}</span></div>
                        @forelse($activity as $row)
                            <div class="todo-row">
                                <span class="badge">{{ $row['kind_label'] }}</span>
                                <div class="grow">
                                    @if($row['url'])
                                        <a href="{{ $row['url'] }}">{{ $row['title'] }}</a>
                                    @else
                                        {{ $row['title'] }}
                                    @endif
                                    <div class="muted">{{ optional($row['time'])->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="muted dash-empty">{{ admin_text('近 24 小时没有新发布或采集写入。') }}</p>
                        @endforelse
                    </div>
                @endif
                <div class="card card-panel">
                    <div class="card-header"><span>{{ admin_text('异常') }}</span></div>
                    @forelse($alerts as $alert)
                        <div class="todo-row">
                            <span class="badge {{ $alert['level'] === 'warn' ? 'badge-warn' : 'badge-off' }}">{{ $alert['level'] === 'warn' ? admin_text('注意') : admin_text('提示') }}</span>
                            <div class="grow">
                                @if($alert['url'])
                                    <a href="{{ $alert['url'] }}">{{ $alert['title'] }}</a>
                                @else
                                    {{ $alert['title'] }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="muted dash-empty">{{ admin_text('暂时没有需要盯的异常。') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <div class="card card-panel">
        <div class="card-header">
            <span>{{ admin_text('接下来做什么') }}</span>
            <a class="btn btn-sm btn-muted" href="{{ route('admin.quick-panels.edit') }}">{{ admin_text('配置') }}</a>
        </div>
        <div class="card-body">
            <div class="dash-actions">
                @forelse($quickNav as $item)
                    <a class="btn {{ $item['key'] === 'entries_write' ? '' : 'btn-muted' }}" href="{{ $item['url'] }}">
                        <i class="{{ $item['icon'] }}"></i> {{ $item['title'] }}
                    </a>
                @empty
                    <p class="muted" style="margin:0">{{ admin_text('暂无快捷入口，请先') }} <a href="{{ route('admin.quick-panels.edit') }}">{{ admin_text('配置') }}</a>{{ $dashCjkStop ? '。' : '.' }}</p>
                @endforelse
            </div>
            @if($quickOverflow > 0)
                <p class="muted" style="margin:10px 0 0">{{ admin_text('还有 :n 个入口在配置里，首页只保留高频动作。', ['n' => $quickOverflow]) }}</p>
            @endif
        </div>
    </div>

    @if($health)
        <details class="dash-health" @if($health['attention']) open @endif>
            <summary>
                <span>{{ admin_text('站点健康') }}</span>
                <span class="muted" style="font-weight:400">
                    {{ admin_text('缓存') }} {{ $health['cache'] ? admin_text('开') : admin_text('关') }}
                    · {{ admin_text('采集') }} {{ ['schedule' => admin_text('自动调度'), 'queue' => admin_text('队列处理'), 'off' => admin_text('已关闭')][$health['collect_mode']] ?? $health['collect_mode'] }}
                    @if($health['last_run'])
                        · {{ admin_text('上次') }} {{ $health['last_ok'] ? admin_text('成功') : admin_text('失败') }}
                    @endif
                </span>
                @if($health['attention'])
                    <span class="badge badge-warn">{{ admin_text('采集需要处理') }}</span>
                    @if(auth('admin')->user()?->canPerm('collectors'))
                        <a href="{{ route('admin.collectors.index') }}">{{ admin_text('查看采集任务') }}</a>
                    @endif
                @else
                    <span class="badge badge-ok">{{ admin_text('正常') }}</span>
                @endif
            </summary>
            <table class="data info" style="border:0">
                <tr>
                    <th>{{ admin_text('主题') }}</th>
                    <td>{{ $health['theme'] }}</td>
                    <th>{{ admin_text('全页缓存') }}</th>
                    <td>
                        @if($health['cache'])
                            <span class="badge badge-ok">{{ admin_text('已启用') }}</span>
                        @else
                            <span class="badge badge-off">{{ admin_text('未启用') }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ admin_text('采集模式') }}</th>
                    <td><span class="badge">{{ ['schedule' => admin_text('自动调度'), 'queue' => admin_text('队列处理'), 'off' => admin_text('已关闭')][$health['collect_mode']] ?? $health['collect_mode'] }}</span></td>
                    <th>{{ admin_text('上次采集') }}</th>
                    <td>
                        @if($health['last_run'])
                            {{ $health['last_run'] }}
                            @if($health['last_ok'])
                                <span class="badge badge-ok">{{ admin_text('成功') }}</span>
                            @else
                                <span class="badge badge-warn">{{ admin_text('失败') }}</span>
                            @endif
                            @if($health['last_message'])
                                <div class="muted">{{ $health['last_message'] }}</div>
                            @endif
                        @else
                            <span class="muted">{{ admin_text('还没有采集记录') }}</span>
                        @endif
                    </td>
                </tr>
                @if($health['schedule'])
                    <tr>
                        <th>{{ admin_text('调度') }}</th>
                        <td colspan="3">{{ $health['schedule'] }}</td>
                    </tr>
                @endif
            </table>
        </details>
    @endif
@endsection
