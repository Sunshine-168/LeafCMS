<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cms\StatHit;
use App\Services\Cms\Stats\PageTitleResolver;
use App\Services\Cms\Stats\StatService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatController extends Controller
{
    public function __construct(private readonly StatService $stats)
    {
    }

    /** 流量概览 */
    public function index(Request $request): View
    {
        [$from, $to] = $this->range($request);
        $overview = $this->stats->overview();
        $today = $overview['today'];
        $yesterday = $overview['yesterday'];

        return view('admin.stats.index', [
            'overview' => $overview,
            'changes' => [
                'pv' => $this->stats->change($today['pv'], $yesterday['pv']),
                'uv' => $this->stats->change($today['uv'], $yesterday['uv']),
                'ip' => $this->stats->change($today['ip'], $yesterday['ip']),
                'spider_pv' => $this->stats->change($today['spider_pv'], $yesterday['spider_pv']),
            ],
            'weekCompare' => $this->stats->last7VersusPrev7(),
            'rangeCompare' => $this->stats->compareRange($from, $to),
            'clients' => $this->stats->clientBreakdown($from, $to),
            'trend' => $this->stats->dailyTrend(14),
            'hourly' => $this->stats->hourlyDistribution(7),
            'topPages' => $this->stats->topPages($from, $to),
            'topErrors' => $this->stats->topErrorPages($from, $to),
            'topReferers' => $this->stats->topReferers($from, $to),
            'refererHosts' => $this->stats->topRefererHosts($from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** 导出当前筛选的流量摘要 */
    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $overview = $this->stats->overview();
        $range = $this->stats->compareRange($from, $to);
        $week = $this->stats->last7VersusPrev7();
        $pages = $this->stats->topPages($from, $to);
        $hosts = $this->stats->topRefererHosts($from, $to, 20);
        $clients = $this->stats->clientBreakdown($from, $to);

        $filename = 'cms-stats-'.$from.'-'.$to.'.csv';

        return response()->streamDownload(function () use ($overview, $range, $week, $pages, $hosts, $clients) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $write = function (array $row) use ($out) {
                fputcsv($out, $row);
            };

            $write([admin_text('概览')]);
            $write([admin_text('时段'), 'PV', 'UV', admin_text('独立 IP'), admin_text('蜘蛛 PV')]);
            foreach (['today' => '今日', 'yesterday' => '昨日', 'days7' => '近7天', 'days30' => '近30天'] as $key => $label) {
                $row = $overview[$key];
                $write([admin_text($label), $row['pv'], $row['uv'], $row['ip'], $row['spider_pv']]);
            }
            $write([]);
            $write([admin_text('近7日 vs 前7日'), $week['from'].' ~ '.$week['to'], admin_text('对比'), $week['prev_from'].' ~ '.$week['prev_to']]);
            $write([admin_text('指标'), admin_text('本期'), admin_text('上期'), admin_text('变化')]);
            foreach (['pv' => 'PV', 'uv' => 'UV'] as $key => $label) {
                $write([$label, $week['current'][$key], $week['previous'][$key], $week['changes'][$key]['text']]);
            }
            $write([]);
            $write([admin_text('所选时段'), $range['from'].' ~ '.$range['to'], admin_text('上期'), $range['prev_from'].' ~ '.$range['prev_to']]);
            $write([admin_text('指标'), admin_text('本期'), admin_text('上期'), admin_text('变化')]);
            foreach (['pv' => 'PV', 'uv' => 'UV', 'ip' => admin_text('独立 IP'), 'spider_pv' => admin_text('蜘蛛 PV')] as $key => $label) {
                $write([$label, $range['current'][$key], $range['previous'][$key], $range['changes'][$key]['text']]);
            }
            $write([]);
            $write([admin_text('热门页面')]);
            $write([admin_text('标题'), admin_text('路径'), 'PV']);
            foreach ($pages as $page) {
                $write([$page['title'], $page['path'], $page['hits']]);
            }
            $write([]);
            $write([admin_text('来路域名')]);
            $write([admin_text('来源'), admin_text('次数')]);
            foreach ($hosts as $host) {
                $write([$host['host'], $host['hits']]);
            }
            $write([]);
            $write([admin_text('设备')]);
            $write([admin_text('类型'), admin_text('次数')]);
            foreach ($clients['devices'] as $row) {
                $write([$row['name'], $row['hits']]);
            }
            $write([]);
            $write([admin_text('浏览器')]);
            $write([admin_text('名称'), admin_text('次数')]);
            foreach ($clients['browsers'] as $row) {
                $write([$row['name'], $row['hits']]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** 蜘蛛统计 */
    public function spiders(Request $request): View
    {
        [$from, $to] = $this->range($request);
        $spider = $request->string('bot')->toString() ?: null;
        $status = $request->filled('status') ? $request->integer('status') : null;
        $errors = $this->stats->spiderErrors($from, $to);
        $recent = $this->stats->spiderRecent($from, $to, $spider, $status);

        return view('admin.stats.spiders', [
            'kpis' => $this->stats->spiderKpis($from, $to),
            'composition' => $this->stats->spiderComposition($from, $to),
            'trend' => $this->stats->spiderGroupTrend(14),
            'summary' => $this->stats->spiderSummary($from, $to),
            'topPages' => $this->stats->topSpiderPages($from, $to),
            'errors' => $errors,
            'recent' => $recent,
            'pathTitles' => app(PageTitleResolver::class)->forPaths($recent->pluck('path')->merge($errors->pluck('path'))),
            'spiderNames' => $this->stats->spiderNames($from, $to),
            'from' => $from,
            'to' => $to,
            'bot' => $spider,
            'status' => $status,
        ]);
    }

    public function exportSpiders(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $kpis = $this->stats->spiderKpis($from, $to);
        $summary = $this->stats->spiderSummary($from, $to);
        $pages = $this->stats->topSpiderPages($from, $to);
        $errors = $this->stats->spiderErrors($from, $to, 200);

        $filename = 'cms-spiders-'.$from.'-'.$to.'.csv';

        return response()->streamDownload(function () use ($kpis, $summary, $pages, $errors, $from, $to) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $write = fn (array $row) => fputcsv($out, $row);
            $write([admin_text('时段'), $from.' ~ '.$to]);
            $write([admin_text('今日蜘蛛 PV'), $kpis['pv'], $kpis['change']['text']]);
            $write([admin_text('本期独立URL'), $kpis['urls']]);
            $write([admin_text('非200次数'), $kpis['errors']]);
            $write([admin_text('工具+AI占比'), $kpis['tool_ai_share'].'%']);
            $write([]);
            $write([admin_text('搜索引擎到访')]);
            $write([admin_text('引擎'), admin_text('今日'), admin_text('状态'), admin_text('距上次天数')]);
            foreach ($kpis['presence'] as $row) {
                $write([$row['label'], $row['today'] ? admin_text('是') : admin_text('否'), $row['status'], $row['absent_days'] ?? '']);
            }
            $write([]);
            $write([admin_text('蜘蛛汇总')]);
            $write([admin_text('蜘蛛'), admin_text('分类'), admin_text('次数'), admin_text('独立 IP'), admin_text('占比')]);
            foreach ($summary as $row) {
                $write([$row['spider_name'], $row['group_label'], $row['hits'], $row['ips'], $row['share']]);
            }
            $write([]);
            $write([admin_text('被爬最多的页')]);
            $write([admin_text('标题'), admin_text('路径'), admin_text('次数')]);
            foreach ($pages as $page) {
                $write([$page['title'], $page['path'], $page['hits']]);
            }
            $write([]);
            $write([admin_text('非 200 抓取')]);
            $write([admin_text('时间'), admin_text('蜘蛛'), admin_text('路径'), admin_text('状态')]);
            foreach ($errors as $row) {
                $write([(string) $row->created_at, $row->spider_name, $row->path, $row->status_code]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** 明细日志 */
    public function logs(Request $request): View
    {
        $filters = $this->logFilters($request);
        $page = $this->stats->filteredLogs($filters)->paginate(20)->withQueryString();
        $titles = app(PageTitleResolver::class)->forPaths($page->getCollection()->pluck('path'));
        $rows = $page->getCollection()->map(
            fn (StatHit $hit) => $this->stats->presentLog($hit, $titles[$hit->path] ?? null)
        );

        $trail = $filters['hash'] !== ''
            ? $this->stats->visitorTrail($filters['hash'], $filters['from'], $filters['to'])
            : collect();
        $errorCount = $this->stats->filteredLogs($filters)->where('status_code', '!=', 200)->count();

        return view('admin.stats.logs', [
            'logs' => $page,
            'rows' => $rows,
            'filters' => $filters,
            'activeFilters' => $this->activeLogFilters($filters),
            'trail' => $trail,
            'errorCount' => $errorCount,
        ]);
    }

    public function exportLogs(Request $request): StreamedResponse
    {
        $filters = $this->logFilters($request);
        $hits = $this->stats->filteredLogs($filters)->limit(5000)->get();
        $titles = app(PageTitleResolver::class)->forPaths($hits->pluck('path'));
        $filename = 'cms-logs-'.$filters['from'].'-'.$filters['to'].'.csv';

        return response()->streamDownload(function () use ($hits, $titles) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [admin_text('时间'), admin_text('访问者'), admin_text('标题'), admin_text('路径'), 'IP', admin_text('来路'), admin_text('客户端'), admin_text('状态'), admin_text('本机探测')]);
            foreach ($hits as $hit) {
                $row = $this->stats->presentLog($hit, $titles[$hit->path] ?? null);
                fputcsv($out, [
                    (string) $row['at'],
                    $row['visitor'],
                    $row['title'],
                    $row['path'],
                    $row['ip'],
                    $row['referer'],
                    $row['client'],
                    $row['status'],
                    $row['local'] ? admin_text('是') : '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{from:string,to:string,visitor:string,bot:string,path:string,status:string,ip:string,hash:string,probe:string} */
    protected function logFilters(Request $request): array
    {
        [$from, $to] = $this->range($request);
        $visitor = (string) $request->input('visitor', '');
        if ($visitor === '' && $request->filled('spider')) {
            $visitor = $request->boolean('spider') ? 'spider' : 'human';
        }
        if (! in_array($visitor, ['', 'human', 'spider'], true)) {
            $visitor = '';
        }
        $status = (string) $request->input('status', '');
        if (! in_array($status, ['', '200', '404', 'other'], true)) {
            $status = '';
        }

        return [
            'from' => $from,
            'to' => $to,
            'visitor' => $visitor,
            'bot' => $request->string('bot')->toString(),
            'path' => $request->string('path')->toString(),
            'status' => $status,
            'ip' => $request->string('ip')->toString(),
            'hash' => $request->string('hash')->toString(),
            'probe' => $request->string('probe')->toString() === 'hide' ? 'hide' : '',
        ];
    }

    /** @param  array{from:string,to:string,visitor:string,bot:string,path:string,status:string,ip:string,hash:string,probe:string}  $filters */
    protected function activeLogFilters(array $filters): int
    {
        $count = 0;
        foreach (['visitor', 'bot', 'path', 'status', 'ip', 'hash', 'probe'] as $key) {
            if ($filters[$key] !== '') {
                $count++;
            }
        }

        return $count;
    }

    /** @return array{0:string,1:string} */
    protected function range(Request $request): array
    {
        $to = $request->input('to', now()->toDateString());
        $from = $request->input('from', now()->subDays(6)->toDateString());
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}
