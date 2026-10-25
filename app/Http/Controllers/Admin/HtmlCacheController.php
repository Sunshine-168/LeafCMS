<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SettingService;
use App\Services\Cms\DiskHtmlService;
use App\Services\Cms\HtmlCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HtmlCacheController extends Controller
{
    public function __construct(
        private readonly HtmlCacheService $htmlCache,
        private readonly DiskHtmlService $diskHtml,
        private readonly SettingService $settings,
    ) {}

    /** 全页缓存管理 */
    public function edit(): View
    {
        $cfg = $this->settings->getMany(['html_cache_enabled', 'html_cache_ttl']);
        $ttl = ($cfg['html_cache_ttl'] !== null && $cfg['html_cache_ttl'] !== '')
            ? (int) $cfg['html_cache_ttl']
            : (int) config('cms.html_cache.ttl', 3600);
        $lastBust = $this->htmlCache->lastBust();
        $ttlOptions = HtmlCacheService::ttlOptions();
        if (! array_key_exists($ttl, $ttlOptions)) {
            $ttlOptions[$ttl] = admin_text('当前 :label', ['label' => $this->ttlPlain($ttl)]);
        }

        return view('admin.html_cache.edit', [
            'enabled' => ($cfg['html_cache_enabled'] !== null && $cfg['html_cache_enabled'] !== '')
                ? (bool) (int) $cfg['html_cache_enabled']
                : (bool) config('cms.html_cache.enabled'),
            'ttl' => $ttl,
            'ttlOptions' => $ttlOptions,
            'lastBust' => $lastBust,
            'lastBustLabel' => $this->htmlCache->bustLabel($lastBust),
            'diskEnabled' => $this->diskHtml->enabled(),
            'diskCount' => $this->diskHtml->fileCount(),
            'diskJob' => $this->diskHtml->publicJob(),
        ]);
    }

    /** 打开或关闭磁盘静态页 */
    public function updateDisk(Request $request): RedirectResponse
    {
        $on = $request->boolean('disk_html_enabled');
        $this->settings->save([
            'disk_html_enabled' => $on ? '1' : '0',
        ]);

        return redirect()->route('admin.html-cache.edit')->with(
            'status',
            $on ? admin_text('已打开磁盘静态页，可以开始生成') : admin_text('已关闭磁盘静态页')
        );
    }

    /** 保存开关与保存时长 */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'html_cache_ttl' => 'nullable|integer|min:0|max:2592000',
        ]);

        $this->settings->save([
            'html_cache_enabled' => $request->boolean('html_cache_enabled') ? '1' : '0',
            'html_cache_ttl' => (string) max(0, (int) $request->input('html_cache_ttl', 3600)),
        ]);

        return redirect()->route('admin.html-cache.edit')->with(
            'status',
            $request->boolean('html_cache_enabled') ? admin_text('已开启全页缓存') : admin_text('已关闭全页缓存')
        );
    }

    /** 清空 */
    public function clear(): RedirectResponse
    {
        $this->htmlCache->forgetAll('admin:clear');

        return redirect()->route('admin.html-cache.edit')->with('status', admin_text('已清空。访客下一次打开会重新生成页面。'));
    }

    /** 预热 */
    public function warm(Request $request): RedirectResponse
    {
        if (! $this->htmlCache->enabled()) {
            return redirect()->route('admin.html-cache.edit')->with('error', admin_text('请先打开全页缓存并保存'));
        }

        $result = $this->htmlCache->warm((int) $request->input('entries', 30));
        $msg = $result['fail'] > 0
            ? admin_text('已预热 :ok 页，:fail 页没生成好', ['ok' => $result['ok'], 'fail' => $result['fail']])
            : admin_text('已预热 :ok 页', ['ok' => $result['ok']]);

        return redirect()->route('admin.html-cache.edit')->with('status', $msg);
    }

    /** 开始生成磁盘 HTML（JSON 走进度；普通提交一次写完） */
    public function buildDisk(Request $request): JsonResponse|RedirectResponse
    {
        if (! $this->diskHtml->enabled()) {
            $msg = admin_text('请先打开磁盘静态页并保存');
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg, 'status' => 'error'], 422);
            }

            return redirect()->route('admin.html-cache.edit')->with('error', $msg);
        }

        $limit = (int) $request->input('entries', 0);
        if ($request->expectsJson()) {
            $job = $this->diskHtml->startJob($limit);
            if (! empty($job['conflict'])) {
                return response()->json($job, 422);
            }

            return response()->json($job);
        }

        $result = $this->diskHtml->build($limit > 0 ? $limit : 200);
        $msg = $result['fail'] > 0
            ? admin_text('已写成 :ok 个静态文件，:fail 页没写上', ['ok' => $result['ok'], 'fail' => $result['fail']])
            : admin_text('已写成 :ok 个静态文件', ['ok' => $result['ok']]);

        return redirect()->route('admin.html-cache.edit')->with('status', $msg);
    }

    /** 再处理一批（生成或删除） */
    public function stepDisk(Request $request): JsonResponse
    {
        $job = $this->diskHtml->publicJob();
        $kind = $job['kind'] ?? 'build';
        if ($kind !== 'clear' && ! $this->diskHtml->enabled()) {
            return response()->json(['message' => admin_text('请先打开磁盘静态页并保存'), 'status' => 'error'], 422);
        }

        $chunk = (int) $request->input('chunk', $kind === 'clear' ? 40 : 6);

        return response()->json($this->diskHtml->stepJob($chunk));
    }

    /** 当前生成进度 */
    public function diskStatus(): JsonResponse
    {
        return response()->json($this->diskHtml->publicJob());
    }

    /** 停下正在生成的任务 */
    public function cancelDisk(): JsonResponse
    {
        return response()->json($this->diskHtml->cancelJob());
    }

    /** 清空磁盘 HTML（JSON 走进度；普通提交一次删完） */
    public function clearDisk(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            $job = $this->diskHtml->startClearJob();
            if (! empty($job['conflict'])) {
                return response()->json($job, 422);
            }

            return response()->json($job);
        }

        $n = $this->diskHtml->clear();

        return redirect()->route('admin.html-cache.edit')->with(
            'status',
            $n > 0 ? admin_text('已删掉 :n 个静态文件', ['n' => $n]) : admin_text('没有可删的静态文件')
        );
    }

    protected function ttlPlain(int $seconds): string
    {
        if ($seconds <= 0) {
            return admin_text('改内容后马上换新');
        }
        if ($seconds % 86400 === 0) {
            return admin_text(':n 天', ['n' => $seconds / 86400]);
        }
        if ($seconds % 3600 === 0) {
            return admin_text(':n 小时', ['n' => $seconds / 3600]);
        }
        if ($seconds % 60 === 0) {
            return admin_text(':n 分钟', ['n' => $seconds / 60]);
        }

        return admin_text(':n 秒', ['n' => $seconds]);
    }
}
