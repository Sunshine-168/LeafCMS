<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SettingService;
use App\Services\Admin\SiteBackupService;
use App\Services\Cms\ThemeCatalog;
use App\Support\CurrentSite;
use App\Support\SettingSections;
use App\Support\ThemeAppearance;
use App\Support\UrlMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly SiteBackupService $backups,
    ) {}

    /** 站点设置页 */
    public function edit(Request $request): View
    {
        $keys = [
            'site_name', 'site_description', 'site_keywords', 'theme',
            'upload_thumb_width', 'upload_watermark_text', 'upload_watermark_position', 'upload_watermark_image',
            'site_closed', 'site_closed_message',
            'analytics_code', 'tag_autolink',
            'mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_password',
            'mail_from_address', 'mail_from_name',
            'url_mode', 'mobile_theme', 'mobile_domain', 'mobile_detect',
            'api_enable', 'api_token',
            'comments_enabled', 'comments_guest', 'comments_auto_approve',
            'comments_notify_email', 'comments_rate_seconds',
            'map_default_lng', 'map_default_lat',
            'cdn_url',
            'robots_extra',
            'logo', 'favicon', 'primary_color', 'footer_text', 'icp', 'hero_title', 'hero_subtitle',
        ];

        return view('admin.settings.edit', [
            'settings' => $this->settingService->getMany($keys),
            'tab' => $this->tabFrom($request),
            'site' => CurrentSite::model(),
        ]);
    }

    /** 保存站点设置 */
    public function update(Request $request): RedirectResponse
    {
        $section = $request->input('section');
        if ($section !== null) {
            $request->validate(['section' => ['required', Rule::in(array_keys(SettingSections::LABELS))]]);
        }
        $allowed = $section ? SettingSections::FIELDS[$section] : array_unique(array_merge(...array_values(SettingSections::FIELDS)));
        $data = $request->only($allowed);
        foreach (SettingSections::CHECKBOXES as $key) {
            if (($section && in_array($key, $allowed, true)) || array_key_exists($key, $data)) {
                $data[$key] = $request->boolean($key) ? '1' : '0';
            }
        }
        $themes = array_column(array_filter(app(ThemeCatalog::class)->all(), fn ($theme) => $theme['complete']), 'slug');
        Validator::make($data, [
            'site_name' => 'sometimes|required|string|max:120',
            'site_description' => 'nullable|string|max:2000',
            'site_keywords' => 'nullable|string|max:500',
            'site_closed_message' => 'nullable|string|max:1000',
            'api_token' => 'nullable|string|max:512',
            'mail_host' => 'nullable|string|max:255',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:1000',
            'mail_from_name' => 'nullable|string|max:255',
            'theme' => ['sometimes', 'required', Rule::in($themes)],
            'mobile_theme' => ['nullable', Rule::in($themes)],
            'url_mode' => ['sometimes', Rule::in(array_keys(UrlMode::options()))],
            'mobile_detect' => 'sometimes|in:both,domain,ua',
            'upload_watermark_position' => 'sometimes|in:br,bl,tr,tl,center',
            'upload_thumb_width' => 'nullable|integer|min:0|max:5000',
            'comments_rate_seconds' => 'nullable|integer|min:10|max:3600',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_from_address' => 'nullable|email',
            'comments_notify_email' => 'nullable|email',
            'cdn_url' => 'nullable|url:http,https',
            'map_default_lng' => 'nullable|numeric|between:-180,180',
            'map_default_lat' => 'nullable|numeric|between:-90,90',
            'watermark_image' => 'nullable|image|max:2048',
            'logo_file' => 'nullable|image|max:2048',
            'favicon_file' => 'nullable|image|max:512',
        ])->validate();
        $api = $this->settingService->getMany(['api_enable', 'api_token']);
        if (($data['api_enable'] ?? $api['api_enable']) === '1' && (array_key_exists('api_enable', $data) || array_key_exists('api_token', $data))) {
            $token = (string) (array_key_exists('api_token', $data) ? $data['api_token'] : $api['api_token']);
            if (trim($token) === '') {
                throw ValidationException::withMessages(['api_token' => admin_text('启用接口密钥保护前，请填写接口密钥')]);
            }
        }
        if (array_key_exists('mail_password', $data) && ! $request->filled('mail_password')) {
            unset($data['mail_password']);
        }
        foreach (['upload_thumb_width', 'comments_rate_seconds', 'mail_port'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = (string) ($data[$key] ?? match ($key) {
                    'mail_port' => 587, 'comments_rate_seconds' => 60, default => 0,
                });
            }
        }
        if (array_key_exists('cdn_url', $data)) {
            $data['cdn_url'] = rtrim(trim((string) $data['cdn_url']), '/');
        }
        if (array_key_exists('robots_extra', $data)) {
            $data['robots_extra'] = $this->sanitizeRobotsExtra((string) $data['robots_extra']);
        }
        if (array_key_exists('primary_color', $data)) {
            $data['primary_color'] = ThemeAppearance::sanitizeColor((string) $data['primary_color']);
        }
        foreach (['icp' => 80, 'footer_text' => 500, 'hero_title' => 120, 'hero_subtitle' => 200] as $key => $max) {
            if (array_key_exists($key, $data)) {
                $data[$key] = mb_substr(trim((string) $data[$key]), 0, $max);
            }
        }
        foreach ([
            'watermark_image' => ['upload_watermark_image', 'upload_watermark_image_clear', 'cms/watermark'],
            'logo_file' => ['logo', 'logo_clear', 'cms/brand'],
            'favicon_file' => ['favicon', 'favicon_clear', 'cms/brand'],
        ] as $file => [$key, $clear, $directory]) {
            if (in_array($file, $allowed, true) && $request->boolean($clear)) {
                $data[$key] = '';
            }
            if (in_array($file, $allowed, true) && $request->hasFile($file)) {
                $data[$key] = $request->file($file)->store($directory, 'public');
            }
            unset($data[$file], $data[$clear]);
        }

        $result = $this->settingService->save($data);
        if (array_key_exists('theme', $data) && ($site = CurrentSite::model()) && $site->theme) {
            // 外观设置作为唯一入口，移除旧站点记录上的优先覆盖。
            $site->theme = null;
            $site->save();
        }
        $tab = $section ?: $this->tabFrom($request);

        return redirect()->route('admin.settings.edit', $tab === 'site' ? [] : ['tab' => $tab])
            ->with('status', $result['msg']);
    }

    private function tabFrom(Request $request): string
    {
        $tab = (string) $request->input('tab', $request->query('tab', 'site'));

        return SettingSections::normalize($tab);
    }

    public function maintenance(): View
    {
        abort_unless(auth('admin')->user()?->isSuperAdmin(), 403);

        return view('admin.settings.maintenance', [
            'backups' => $this->backups->list(),
            'canSeedDemo' => app()->environment('local', 'testing'),
        ]);
    }

    protected function sanitizeRobotsExtra(string $raw): string
    {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $lines = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, '<') || str_contains($line, '>')) {
                continue;
            }
            $lines[] = mb_substr($line, 0, 200);
            if (count($lines) >= 40) {
                break;
            }
        }

        return implode("\n", $lines);
    }
}
