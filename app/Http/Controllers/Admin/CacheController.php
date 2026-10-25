<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SiteBackupService;
use App\Services\Cms\HtmlCacheService;
use Database\Seeders\CmsSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CacheController extends Controller
{
    public function __construct(
        private readonly HtmlCacheService $htmlCache,
        private readonly SiteBackupService $backups,
    ) {}

    /** 旧「系统工具」入口，改走缓存页 */
    public function edit(): RedirectResponse
    {
        return redirect()->route('admin.html-cache.edit');
    }

    /** 一键清理配置 / 视图 / 应用 / 全页缓存 */
    public function clear(): RedirectResponse
    {
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        $this->htmlCache->forgetAll('admin:global-clear');

        return redirect()->route('admin.html-cache.edit')->with('status', admin_text('缓存已清空'));
    }

    /** 重新导入演示数据（幂等 updateOrCreate） */
    public function seedDemo(): RedirectResponse
    {
        $this->assertMaintenanceAccess();
        abort_unless(app()->environment('local', 'testing'), 403, admin_text('正式环境不允许导入演示数据'));
        try {
            Artisan::call('db:seed', ['--class' => CmsSeeder::class, '--force' => true]);
            Artisan::call('db:seed', ['--class' => DemoDataSeeder::class, '--force' => true]);
            if (! is_file(storage_path('app/install.lock'))) {
                file_put_contents(storage_path('app/install.lock'), 'installed_at='.now()->toDateTimeString()."\nseed_demo=1\n");
            }
        } catch (\Throwable $e) {
            return $this->toSettingsMore()->with('error', admin_text('导入失败：:message', ['message' => $e->getMessage()]));
        }

        return $this->toSettingsMore()->with('status', admin_text('演示数据已更新'));
    }

    public function backup(): RedirectResponse
    {
        $this->assertMaintenanceAccess();
        $result = $this->backups->create();

        return $this->toSettingsMore()
            ->with($result['ok'] ? 'status' : 'error', $result['msg']);
    }

    public function downloadBackup(string $file): BinaryFileResponse|RedirectResponse
    {
        $this->assertMaintenanceAccess();
        $path = $this->backups->pathFor($file);
        if (! $path) {
            return $this->toSettingsMore()->with('error', admin_text('备份不存在'));
        }

        return response()->download($path, $file);
    }

    public function restoreBackup(Request $request, string $file): RedirectResponse
    {
        $this->assertMaintenanceAccess();
        $request->validate(['confirmation' => ['required', Rule::in(['恢复全部站点', '恢復全部站點', 'RESTORE ALL SITES'])]]);
        $result = $this->backups->restore($file);

        return $this->toSettingsMore()
            ->with($result['ok'] ? 'status' : 'error', $result['msg']);
    }

    protected function toSettingsMore(): RedirectResponse
    {
        return redirect()->route('admin.settings.maintenance');
    }

    private function assertMaintenanceAccess(): void
    {
        abort_unless(auth('admin')->user()?->isSuperAdmin(), 403, admin_text('全系统维护仅限超级管理员'));
    }
}
