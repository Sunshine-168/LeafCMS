<?php

namespace App\Services\Admin;

use App\Models\Admin\AdminUser;
use App\Models\Cms\Collector;
use App\Models\Cms\Comment;
use App\Models\Cms\Entry;
use App\Models\Cms\FormSubmission;
use App\Models\Cms\StatHit;
use App\Services\Cms\HtmlCacheService;
use App\Services\Cms\Stats\StatService;
use App\Services\Cms\ThemeCatalog;
use App\Support\AdminCategoryScope;
use App\Support\CmsRuntime;
use App\Support\CurrentSite;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DashboardService
{
    public function __construct(
        private readonly StatService $stats,
        private readonly HtmlCacheService $htmlCache,
        private readonly QuickPanelService $quickPanels,
        private readonly EntryService $entries,
        private readonly FormSubmissionAdminService $forms,
        private readonly CommentAdminService $comments,
    ) {}

    /**
     * @return array{
     *     modules: array<string, bool>,
     *     kpis: list<array<string, mixed>>,
     *     trend: Collection,
     *     todos: list<array<string, mixed>>,
     *     todoCounts: array{entries:int,forms:int,comments:int,total:int},
     *     activity: list<array<string, mixed>>,
     *     alerts: list<array<string, mixed>>,
     *     health: array<string, mixed>|null,
     *     quickNav: list<array{key:string,title:string,icon:string,url:string}>,
     *     quickOverflow: int
     * }
     */
    public function forAdmin(AdminUser $admin): array
    {
        $modules = $this->modules($admin);
        $todoCounts = $this->todoCounts($admin, $modules);
        $nav = $this->quickPanels->forAdmin($admin);
        $quickNav = array_slice($nav, 0, 8);

        return [
            'modules' => $modules,
            'kpis' => $this->kpis($admin, $modules, $todoCounts),
            'trend' => $modules['traffic'] ? $this->stats->dailyTrend(7) : collect(),
            'todos' => $this->todos($admin),
            'todoCounts' => $todoCounts,
            'activity' => $this->activity($modules),
            'alerts' => $this->alerts($admin, $modules),
            'health' => $modules['health'] ? $this->health() : null,
            'quickNav' => $quickNav,
            'quickOverflow' => max(0, count($nav) - count($quickNav)),
            'desk' => $this->desk($admin),
        ];
    }

    /** @return array{traffic:bool,spider:bool,todos:bool,content:bool,activity:bool,health:bool} */
    public function modules(AdminUser $admin): array
    {
        $entries = $admin->canPerm('entries');
        $stats = $admin->canPerm('stats');

        return [
            'traffic' => $stats,
            'spider' => $stats && ($admin->isSuperAdmin() || $admin->canPerm('collectors')),
            'todos' => $entries || $admin->canPerm('form_submissions') || $admin->canPerm('comments'),
            'content' => $entries,
            'activity' => $entries || $admin->canPerm('collectors'),
            'health' => $admin->isSuperAdmin()
                || $admin->canPerm('settings')
                || $admin->canPerm('html_cache')
                || $admin->canPerm('collectors'),
        ];
    }

    /** @return array{entries:int,forms:int,comments:int,total:int} */
    public function todoCounts(AdminUser $admin, array $modules): array
    {
        $siteId = CurrentSite::idOrDefault() ?? 0;
        $epoch = (int) Cache::get(self::inboxEpochKey($siteId), 1);
        $key = "admin.inbox.{$siteId}.{$admin->id}.{$epoch}";

        return Cache::remember($key, 45, function () use ($admin, $modules) {
            $entries = ! empty($modules['content'])
                ? $this->entries->pendingCount() + Entry::query()->where('draft_state', 'pending')->count()
                : 0;
            $forms = $admin->canPerm('form_submissions') ? $this->forms->unreadCount() : 0;
            $comments = $admin->canPerm('comments') ? $this->comments->pendingCount() : 0;

            return [
                'entries' => $entries,
                'forms' => $forms,
                'comments' => $comments,
                'total' => $entries + $forms + $comments,
            ];
        });
    }

    public static function bustInbox(?int $siteId = null): void
    {
        $siteId ??= CurrentSite::idOrDefault() ?? 0;
        $key = self::inboxEpochKey($siteId);
        Cache::put($key, ((int) Cache::get($key, 1)) + 1, 86400);
    }

    protected static function inboxEpochKey(int $siteId): string
    {
        return 'admin.inbox.epoch.'.$siteId;
    }

    /**
     * 顶栏待办：有数才亮，点回仪表盘待办区
     *
     * @return array{entries:int,forms:int,comments:int,total:int,hint:string}
     */
    public function headerInbox(AdminUser $admin): array
    {
        $counts = $this->todoCounts($admin, $this->modules($admin));
        $parts = [];
        if ($counts['entries'] > 0) {
            $parts[] = admin_text('待审').' '.$counts['entries'];
        }
        if ($counts['forms'] > 0) {
            $parts[] = admin_text('留言 :n', ['n' => $counts['forms']]);
        }
        if ($counts['comments'] > 0) {
            $parts[] = admin_text('评论 :n', ['n' => $counts['comments']]);
        }

        return $counts + [
            'hint' => $parts !== [] ? implode(' · ', $parts) : admin_text('没有待办'),
        ];
    }

    /**
     * @param  array{entries:int,forms:int,comments:int,total:int}  $todoCounts
     * @return list<array<string, mixed>>
     */
    protected function kpis(AdminUser $admin, array $modules, array $todoCounts): array
    {
        $cards = [];
        if ($modules['traffic']) {
            $overview = $this->stats->overview();
            $today = $overview['today'];
            $yesterday = $overview['yesterday'];
            $cards[] = [
                'key' => 'pv',
                'label' => admin_text('今日 PV'),
                'value' => $today['pv'],
                'delta' => $this->stats->change($today['pv'], $yesterday['pv']),
                'hint' => null,
                'href' => route('admin.stats.index'),
                'icon' => 'fa-eye',
                'color' => 'blue',
            ];
            $cards[] = [
                'key' => 'uv',
                'label' => admin_text('今日 UV'),
                'value' => $today['uv'],
                'delta' => $this->stats->change($today['uv'], $yesterday['uv']),
                'hint' => null,
                'href' => route('admin.stats.index'),
                'icon' => 'fa-user',
                'color' => 'green',
            ];
        }
        if ($modules['todos']) {
            $parts = [];
            if ($admin->canPerm('entries')) {
                $parts[] = admin_text('待审').' '.$todoCounts['entries'];
            }
            if ($admin->canPerm('form_submissions')) {
                $parts[] = admin_text('留言 :n', ['n' => $todoCounts['forms']]);
            }
            if ($admin->canPerm('comments')) {
                $parts[] = admin_text('评论 :n', ['n' => $todoCounts['comments']]);
            }
            $todoHref = '#dash-todos';
            $cards[] = [
                'key' => 'todo',
                'label' => admin_text('待处理'),
                'value' => $todoCounts['total'],
                'delta' => null,
                'hint' => implode(' · ', $parts),
                'href' => $todoHref,
                'icon' => 'fa-inbox',
                'color' => $todoCounts['total'] > 0 ? 'orange' : 'green',
            ];
        }
        if ($modules['content']) {
            $health = $this->contentHealth();
            $cards[] = [
                'key' => 'content',
                'label' => admin_text('已发布内容'),
                'value' => $health['published'],
                'delta' => null,
                'hint' => admin_text('已发布').' '.$health['published'].' · '.admin_text('定时').' '.$health['scheduled'].' · '.admin_text('草稿').' '.$health['draft'],
                'href' => route('admin.entries.index'),
                'icon' => 'fa-file-alt',
                'color' => 'purple',
            ];
        }
        if ($modules['spider']) {
            $overview = $this->stats->overview();
            $today = $overview['today'];
            $yesterday = $overview['yesterday'];
            $fail = Collector::query()->where('is_active', true)->where('last_status', 'fail')->count();
            $cards[] = [
                'key' => 'spider',
                'label' => admin_text('今日蜘蛛'),
                'value' => $today['spider_pv'],
                'delta' => $this->stats->change($today['spider_pv'], $yesterday['spider_pv']),
                'hint' => $fail > 0 ? admin_text(':n 个采集失败', ['n' => $fail]) : null,
                'href' => route('admin.stats.spiders'),
                'icon' => 'fa-spider',
                'color' => 'orange',
            ];
        }

        return $cards;
    }

    /** @return array{published:int,scheduled:int,draft:int} */
    public function contentHealth(): array
    {
        $apply = function ($q) {
            AdminCategoryScope::applyToEntries($q);

            return $q;
        };

        return [
            'published' => $apply(Entry::query()->published())->count(),
            'scheduled' => $apply(Entry::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '>', now()))->count(),
            'draft' => $apply(Entry::query()->where('status', 'draft'))->count(),
        ];
    }

    /** @return array{drafts:Collection,recent:Collection,scheduled:Collection} */
    public function desk(AdminUser $admin): array
    {
        $empty = collect();
        if (! $admin->canPerm('entries')) {
            return ['drafts' => $empty, 'recent' => $empty, 'scheduled' => $empty];
        }
        $base = Entry::query()->with('modelDef');
        $slugs = app(ThemeCatalog::class)->currentModelSlugs();
        if ($slugs !== []) {
            $base->whereHas('modelDef', fn ($query) => $query->whereIn('slug', $slugs));
        }
        AdminCategoryScope::applyToEntries($base);
        $mine = (int) $admin->id;
        $recent = (clone $base)->where('updated_by', $mine)->orderByDesc('updated_at')->limit(5)->get();
        if ($recent->isEmpty()) {
            $recent = (clone $base)->orderByDesc('updated_at')->limit(5)->get();
        }

        return [
            'drafts' => (clone $base)->where('status', 'draft')->where('updated_by', $mine)->orderByDesc('updated_at')->limit(5)->get(),
            'recent' => $recent,
            'scheduled' => (clone $base)->where('status', 'published')->where('published_at', '>', now())->orderBy('published_at')->limit(5)->get(),
        ];
    }

    /** @return list<array{kind:string,kind_label:string,title:string,time:mixed,url:string}> */
    public function todos(AdminUser $admin): array
    {
        $items = [];

        if ($admin->canPerm('entries')) {
            $q = Entry::query()->where('status', 'pending')->orderByDesc('updated_at')->limit(8);
            AdminCategoryScope::applyToEntries($q);
            foreach ($q->get() as $entry) {
                $items[] = [
                    'kind' => 'entry',
                    'kind_label' => admin_text('待审稿'),
                    'title' => (string) ($entry->t('title') ?: admin_text('未命名')),
                    'time' => $entry->updated_at,
                    'url' => route('admin.entries.edit', $entry),
                ];
            }
            $revisions = Entry::query()->where('draft_state', 'pending')->orderByDesc('updated_at')->limit(8);
            AdminCategoryScope::applyToEntries($revisions);
            foreach ($revisions->get() as $entry) {
                $items[] = [
                    'kind' => 'entry',
                    'kind_label' => admin_text('修改稿'),
                    'title' => (string) ($entry->t('title') ?: admin_text('未命名')),
                    'time' => $entry->updated_at,
                    'url' => route('admin.entries.edit', $entry),
                ];
            }
        }
        if ($admin->canPerm('form_submissions')) {
            foreach (FormSubmission::query()->where('is_read', false)->with('form')->orderByDesc('id')->limit(8)->get() as $row) {
                $items[] = [
                    'kind' => 'form',
                    'kind_label' => admin_text('未读留言'),
                    'title' => $this->submissionTitle($row),
                    'time' => $row->created_at,
                    'url' => route('admin.form-submissions.show', $row),
                ];
            }
        }
        if ($admin->canPerm('comments')) {
            foreach (Comment::query()->pending()->with('entry')->orderByDesc('id')->limit(8)->get() as $row) {
                $entryTitle = $row->entry ? (string) $row->entry->t('title') : admin_text('评论');
                $items[] = [
                    'kind' => 'comment',
                    'kind_label' => admin_text('待审评论'),
                    'title' => $entryTitle.' · '.Str::limit(strip_tags((string) $row->content), 36),
                    'time' => $row->created_at,
                    'url' => route('admin.comments.index', ['status' => 'pending']),
                ];
            }
        }

        usort($items, fn ($a, $b) => ($b['time']?->timestamp ?? 0) <=> ($a['time']?->timestamp ?? 0));

        return array_slice($items, 0, 12);
    }

    /** @return list<array{kind:string,kind_label:string,title:string,time:mixed,url:?string}> */
    public function activity(array $modules): array
    {
        if (empty($modules['activity'])) {
            return [];
        }
        $since = now()->subDay();
        $items = [];

        if (! empty($modules['content'])) {
            $published = Entry::query()
                ->where('status', 'published')
                ->where(function ($q) use ($since) {
                    $q->where('published_at', '>=', $since)
                        ->orWhere(function ($inner) use ($since) {
                            $inner->whereNull('published_at')->where('updated_at', '>=', $since);
                        });
                })
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->limit(5);
            AdminCategoryScope::applyToEntries($published);
            foreach ($published->get() as $entry) {
                $label = admin_text('已发布');
                if ($entry->member_id) {
                    $label = admin_text('会员投稿');
                } elseif ($entry->collector_id) {
                    $label = admin_text('采集写入');
                }
                $items[] = [
                    'kind' => 'publish',
                    'kind_label' => $label,
                    'title' => (string) ($entry->t('title') ?: admin_text('未命名')),
                    'time' => $entry->published_at ?: $entry->updated_at,
                    'url' => route('admin.entries.edit', $entry),
                ];
            }
        }

        usort($items, fn ($a, $b) => ($b['time']?->timestamp ?? 0) <=> ($a['time']?->timestamp ?? 0));

        return array_slice($items, 0, 8);
    }

    /** @return list<array{level:string,title:string,url:?string}> */
    public function alerts(AdminUser $admin, array $modules): array
    {
        $alerts = [];
        if (! empty($modules['health']) || ! empty($modules['spider'])) {
            $failed = Collector::query()->where('is_active', true)->where('last_status', 'fail')->count();
            if ($failed > 0 && $admin->canPerm('collectors')) {
                $alerts[] = [
                    'level' => 'warn',
                    'title' => admin_text(':n 个采集任务最近失败', ['n' => $failed]),
                    'url' => route('admin.collectors.index'),
                ];
            }
        }
        if (! empty($modules['content'])) {
            $scheduled = $this->contentHealth()['scheduled'];
            if ($scheduled >= 3) {
                $alerts[] = [
                    'level' => 'info',
                    'title' => admin_text(':n 篇定时发布还没到点', ['n' => $scheduled]),
                    'url' => route('admin.entries.index', ['status' => 'published']),
                ];
            }
        }
        if (! empty($modules['traffic'])) {
            $today = now()->toDateString();
            $yesterday = now()->subDay()->toDateString();
            $today404 = StatHit::query()->betweenDates($today, $today)->where('status_code', 404)->count();
            $yday404 = StatHit::query()->betweenDates($yesterday, $yesterday)->where('status_code', 404)->count();
            if ($today404 >= 3 && $today404 > $yday404) {
                $alerts[] = [
                    'level' => 'warn',
                    'title' => admin_text('今日 404 :n 次，比昨天多', ['n' => $today404]),
                    'url' => route('admin.stats.logs', ['status' => '404', 'from' => $today, 'to' => $today]),
                ];
            }
        }

        return $alerts;
    }

    /** @return array{theme:string,cache:bool,collect_mode:string,last_run:?string,last_ok:bool,last_message:?string,schedule:?string,attention:bool} */
    public function health(): array
    {
        $latest = Collector::query()->whereNotNull('last_run_at')->orderByDesc('last_run_at')->first();
        $failed = Collector::query()->where('is_active', true)->where('last_status', 'fail')->exists();
        $cacheOn = $this->htmlCache->enabled();
        $mode = CmsRuntime::collectMode();
        $schedule = $this->scheduleHint($mode);

        return [
            'theme' => app(ThemeCatalog::class)->label(app(ThemeCatalog::class)->currentSlug()),
            'cache' => $cacheOn,
            'collect_mode' => $mode,
            'last_run' => $latest?->last_run_at?->toDateTimeString(),
            'last_ok' => $latest ? $latest->last_status !== 'fail' : true,
            'last_message' => $latest?->last_message,
            'schedule' => $schedule,
            'attention' => $failed || $schedule !== null,
        ];
    }

    protected function scheduleHint(string $mode): ?string
    {
        if ($mode === CmsRuntime::MODE_OFF) {
            return Collector::query()->where('is_active', true)->exists()
                ? admin_text('采集已关闭，活动任务不会自动跑')
                : null;
        }
        $overdue = Collector::query()->where('is_active', true)->get()
            ->filter(fn (Collector $c) => $c->due() && $c->last_run_at && $c->last_run_at->lt(now()->subHours(2)));
        if ($overdue->isNotEmpty()) {
            return admin_text('有采集已过期超过 2 小时，调度可能没在跑');
        }

        return null;
    }

    /** @return array{pv:string,uv:string,width:int,height:int} */
    public function sparkPoints(Collection $trend, int $width = 220, int $height = 56): array
    {
        return [
            'pv' => $this->polyline($trend->pluck('pv')->all(), $width, $height),
            'uv' => $this->polyline($trend->pluck('uv')->all(), $width, $height),
            'width' => $width,
            'height' => $height,
        ];
    }

    /** @param  list<int|float>  $values */
    protected function polyline(array $values, int $width, int $height): string
    {
        $n = count($values);
        if ($n < 2) {
            return '';
        }
        $max = max($values);
        $min = min($values);
        $range = max(1, $max - $min);
        $pts = [];
        foreach ($values as $i => $v) {
            $x = $i / ($n - 1) * $width;
            $y = $height - (($v - $min) / $range) * ($height - 6) - 3;
            $pts[] = round($x, 1).','.round($y, 1);
        }

        return implode(' ', $pts);
    }

    protected function submissionTitle(FormSubmission $row): string
    {
        $form = $row->form?->name ?: admin_text('留言');
        $preview = collect($row->data ?? [])
            ->map(fn ($v) => is_array($v) ? implode(', ', $v) : (string) $v)
            ->filter()
            ->first();

        return $preview ? $form.' · '.Str::limit((string) $preview, 40) : $form;
    }
}
