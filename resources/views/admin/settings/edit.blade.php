@extends('admin.layout')
@section('title', admin_t('nav.settings'))
@section('icon', 'cog')
@php
    $s = $settings;
    $tab = $tab ?? 'site';
    $tabs = \App\Support\SettingSections::labels();
    $logoUrl = ! empty($s['logo']) ? \App\Support\ThemeAppearance::mediaUrl($s['logo']) : '';
    $faviconUrl = ! empty($s['favicon']) ? \App\Support\ThemeAppearance::mediaUrl($s['favicon']) : '';
    $wm = $s['upload_watermark_image'] ?? '';
    $wmUrl = $wm !== '' ? cms_media(\Illuminate\Support\Facades\Storage::disk('public')->url($wm)) : '';
@endphp
@section('content')
    <p class="muted recycle-lead">{{ admin_text('每次只保存当前分组。') }}</p>

    <form method="post" class="settings-page" id="settingsForm" data-unsaved-guard action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="hidden" name="section" value="{{ $tab }}">

        <div class="tabs settings-tabs" id="settingsTabs">
            @foreach($tabs as $key => $label)
                <a class="{{ $tab === $key ? 'active' : '' }}" href="{{ route('admin.settings.edit', $key === 'site' ? [] : ['tab' => $key]) }}">{{ $label }}</a>
            @endforeach
        </div>

        @if($tab === 'site')
        <div class="settings-pane active" data-pane="site">
            <label for="site_name">{{ admin_text('网站名称') }}</label>
            <input id="site_name" type="text" name="site_name" value="{{ old('site_name', $s['site_name'] ?? '') }}" placeholder="{{ admin_text('会出现在浏览器标题和页脚') }}">

            <label for="site_description">{{ admin_text('一句话介绍') }}</label>
            <textarea id="site_description" name="site_description" rows="3">{{ old('site_description', $s['site_description'] ?? '') }}</textarea>
            <p class="muted field-hint">{{ admin_text('搜索引擎和分享卡片会用到。尽量写清网站是做什么的。') }}</p>

            <label for="site_keywords">{{ admin_text('搜索关键词') }}</label>
            <input id="site_keywords" type="text" name="site_keywords" value="{{ old('site_keywords', $s['site_keywords'] ?? '') }}" placeholder="{{ admin_text('用逗号分开，如 新闻,CMS') }}">
            <p class="muted field-hint">{{ admin_text('给搜索引擎看，前台访客一般看不到。') }}</p>

            <label class="inline">
                <input type="checkbox" name="site_closed" value="1" @checked(old('site_closed', $s['site_closed'] ?? '0') == '1')>
                {{ admin_text('暂时关闭网站') }}
            </label>
            <label for="site_closed_message">{{ admin_text('关站时访客看到的说明') }}</label>
            <input id="site_closed_message" type="text" name="site_closed_message" value="{{ old('site_closed_message', $s['site_closed_message'] ?? admin_text('站点维护中，请稍后再访。')) }}">
        </div>
        @endif

        @if($tab === 'look')
        <div class="settings-pane active" data-pane="look">
            <p class="muted field-hint">{{ admin_text('网站外观负责 Logo、颜色和页脚。换模板或复制主题，去「主题与模板」。') }}</p>

            <p class="muted field-hint">{{ admin_text('site_context', ['name' => $site?->displayName()]) }}</p>
            <p class="muted field-hint">{{ admin_text('theme_context', ['desktop' => app(\App\Services\Cms\ThemeCatalog::class)->label($site?->theme ?: ($s['theme'] ?? config('cms.theme'))), 'mobile' => empty($s['mobile_theme']) ? admin_text('跟电脑一样') : app(\App\Services\Cms\ThemeCatalog::class)->label($s['mobile_theme'])]) }}</p>
            @if($site?->theme)
                <p class="muted field-hint">{{ admin_text('电脑主题来自旧站点配置；保存外观后统一使用这里的设置。') }}</p>
            @endif
            <label for="theme">{{ admin_text('外观主题') }}</label>
            <select id="theme" name="theme">
                @foreach(app(\App\Services\Cms\ThemeCatalog::class)->all() as $themeItem)
                    @if($themeItem['complete'])
                    <option value="{{ $themeItem['slug'] }}" @selected(old('theme', $site?->theme ?: ($s['theme'] ?? config('cms.theme'))) === $themeItem['slug'])>{{ $themeItem['name'] }}</option>
                    @endif
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_text('决定首页长什么样。') }} <a href="{{ route('admin.themes.index') }}">{{ admin_text('复制或添加主题') }}</a>.</p>

            <h3>{{ admin_text('手机访问') }}</h3>
            <label for="mobile_theme">{{ admin_text('手机外观') }}</label>
            <select id="mobile_theme" name="mobile_theme">
                <option value="">{{ admin_text('跟电脑一样') }}</option>
                @foreach(app(\App\Services\Cms\ThemeCatalog::class)->all() as $themeItem)
                    @if($themeItem['complete'])
                        <option value="{{ $themeItem['slug'] }}" @selected(old('mobile_theme', $s['mobile_theme'] ?? '') === $themeItem['slug'])>{{ $themeItem['name'] }}</option>
                    @endif
                @endforeach
            </select>
            <label for="mobile_domain">{{ admin_text('手机专用域名') }}</label>
            <input id="mobile_domain" type="text" name="mobile_domain" value="{{ old('mobile_domain', $s['mobile_domain'] ?? '') }}" placeholder="m.example.com">
            <p class="muted field-hint">{{ admin_text('多个域名用逗号分开。留空则只按是否手机浏览器来判断。') }}</p>
            <label for="mobile_detect">{{ admin_text('怎么判断是手机') }}</label>
            <select id="mobile_detect" name="mobile_detect">
                @foreach(['both' => admin_text('手机域名或手机浏览器'), 'domain' => admin_text('只认手机域名'), 'ua' => admin_text('只认手机浏览器')] as $k => $label)
                    <option value="{{ $k }}" @selected(old('mobile_detect', $s['mobile_detect'] ?? 'both') === $k)>{{ $label }}</option>
                @endforeach
            </select>

            <label>{{ admin_text('网站 Logo') }}</label>
            @include('admin.settings._file-preview', [
                'url' => $logoUrl,
                'name' => 'logo_file',
                'empty' => admin_text('还没有 Logo'),
                'clear' => 'logo_clear',
                'kind' => 'logo',
            ])

            <label>{{ admin_text('浏览器小图标') }}</label>
            @include('admin.settings._file-preview', [
                'url' => $faviconUrl,
                'name' => 'favicon_file',
                'empty' => admin_text('还没有小图标'),
                'clear' => 'favicon_clear',
                'kind' => 'favicon',
                'accept' => 'image/png,image/jpeg,image/webp,image/gif,image/x-icon',
            ])
            <p class="muted field-hint">{{ admin_text('出现在浏览器标签上，建议用正方形小图。') }}</p>

            <label for="primary_color">{{ admin_text('主色') }}</label>
            <input id="primary_color" type="text" name="primary_color" value="{{ old('primary_color', $s['primary_color'] ?? '') }}" placeholder="#1b4f72" maxlength="7">
            <p class="muted field-hint">{{ admin_text('如 #1b4f72。留空则用主题自带配色。') }}</p>

            <label for="hero_title">{{ admin_text('首页大标题') }}</label>
            <input id="hero_title" type="text" name="hero_title" value="{{ old('hero_title', $s['hero_title'] ?? '') }}" maxlength="120">
            <label for="hero_subtitle">{{ admin_text('首页副标题') }}</label>
            <input id="hero_subtitle" type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $s['hero_subtitle'] ?? '') }}" maxlength="200">
            <p class="muted field-hint">{{ admin_text('有推荐位大图时，大图优先于这两行字。') }}</p>

            <label for="footer_text">{{ admin_text('页脚补充') }}</label>
            <input id="footer_text" type="text" name="footer_text" value="{{ old('footer_text', $s['footer_text'] ?? '') }}" maxlength="500" placeholder="{{ admin_text('地址、电话或版权说明') }}">

            <label for="icp">{{ admin_text('备案号') }}</label>
            <input id="icp" type="text" name="icp" value="{{ old('icp', $s['icp'] ?? '') }}" maxlength="80" placeholder="{{ admin_text('京ICP备00000000号-1') }}">
        </div>
        @endif

        @if($tab === 'interact')
        <div class="settings-pane active" data-pane="interact">
            <label class="inline">
                <input type="checkbox" name="comments_enabled" value="1" @checked(old('comments_enabled', $s['comments_enabled'] ?? '1') == '1')>
                {{ admin_text('允许在文章下留言') }}
            </label>
            <label class="inline">
                <input type="checkbox" name="comments_guest" value="1" @checked(old('comments_guest', $s['comments_guest'] ?? '1') == '1')>
                {{ admin_text('没登录也能留') }}
            </label>
            <label class="inline">
                <input type="checkbox" name="comments_auto_approve" value="1" @checked(old('comments_auto_approve', $s['comments_auto_approve'] ?? '0') == '1')>
                {{ admin_text('会员留言不用审（游客仍要审）') }}
            </label>

            <label for="comments_notify_email">{{ admin_text('运营通知邮箱') }}</label>
            <input id="comments_notify_email" type="email" name="comments_notify_email" value="{{ old('comments_notify_email', $s['comments_notify_email'] ?? '') }}" placeholder="ops@example.com">
            <p class="muted field-hint">{{ admin_text('新评论、会员待审稿会发到这里。表单另有各自的通知邮箱。留空则不发。') }}</p>

            <label for="comments_rate_seconds">{{ admin_text('同一来源最短间隔（秒）') }}</label>
            <input id="comments_rate_seconds" type="number" name="comments_rate_seconds" min="10" max="3600" value="{{ old('comments_rate_seconds', $s['comments_rate_seconds'] ?? 60) }}">
            <p class="muted field-hint">{{ admin_text('防止刷屏。建议 60 秒。') }}</p>
        </div>
        @endif

        @if($tab === 'urls')
        <div class="settings-pane active" data-pane="urls">
            <h3>{{ admin_text('文章地址') }}</h3>
            <label for="url_mode">{{ admin_text('文章链接怎么写') }}</label>
            <select id="url_mode" name="url_mode">
                @foreach(\App\Support\UrlMode::options() as $mode => $label)
                    <option value="{{ $mode }}" @selected(old('url_mode', $s['url_mode'] ?? 'cat_entry') === $mode)>{{ admin_text($label) }}</option>
                @endforeach
            </select>
            <p class="muted field-hint">{{ admin_text('栏目页仍是「栏目名」。这里只影响单篇文章的地址。') }}</p>

        </div>
        @endif

        @if($tab === 'uploads')
        <div class="settings-pane active" data-pane="uploads">
            <h3>{{ admin_text('图片上传') }}</h3>
            <label for="upload_thumb_width">{{ admin_text('大图缩小到多宽') }}</label>
            <input id="upload_thumb_width" type="number" name="upload_thumb_width" min="0" max="5000" value="{{ old('upload_thumb_width', $s['upload_thumb_width'] ?? 0) }}">
            <p class="muted field-hint">{{ admin_text('单位像素。填 0 表示不缩小。') }}</p>
            <label for="upload_watermark_text">{{ admin_text('文字水印') }}</label>
            <input id="upload_watermark_text" type="text" name="upload_watermark_text" value="{{ old('upload_watermark_text', $s['upload_watermark_text'] ?? '') }}" placeholder="{{ admin_text('© 本站') }}">
            <p class="muted field-hint">{{ admin_text('留空则不加文字。中文显示不正常时，改用下面的图片水印。') }}</p>
            <label for="upload_watermark_position">{{ admin_text('水印位置') }}</label>
            <select id="upload_watermark_position" name="upload_watermark_position">
                @foreach(['br' => admin_text('右下'), 'bl' => admin_text('左下'), 'tr' => admin_text('右上'), 'tl' => admin_text('左上'), 'center' => admin_text('居中')] as $k => $label)
                    <option value="{{ $k }}" @selected(old('upload_watermark_position', $s['upload_watermark_position'] ?? 'br') === $k)>{{ $label }}</option>
                @endforeach
            </select>
            <label>{{ admin_text('图片水印') }}</label>
            @include('admin.settings._file-preview', [
                'url' => $wmUrl,
                'name' => 'watermark_image',
                'empty' => admin_text('还没有水印图'),
                'clear' => 'upload_watermark_image_clear',
                'kind' => 'watermark',
            ])

        </div>
        @endif

        @if($tab === 'mail')
        <div class="settings-pane active" data-pane="mail">
            <h3>{{ admin_text('发信') }}</h3>
            <p class="muted field-hint">{{ admin_text('留言通知、找回密码、待审稿会用到。没配服务器时邮件进日志，前台不会露出重置链接（仅本地调试模式会显示）。密码留空表示不改已保存的值。') }}</p>
            <label for="mail_host">{{ admin_text('邮件服务器') }}</label>
            <input id="mail_host" type="text" name="mail_host" value="{{ old('mail_host', $s['mail_host'] ?? '') }}" placeholder="smtp.example.com">
            <div class="settings-two">
                <div>
                    <label for="mail_port">{{ admin_text('端口') }}</label>
                    <input id="mail_port" type="number" name="mail_port" value="{{ old('mail_port', $s['mail_port'] ?? 587) }}">
                </div>
                <div>
                    <label for="mail_encryption">{{ admin_text('传输加密') }}</label>
                    <select id="mail_encryption" name="mail_encryption">
                        @foreach(['tls' => admin_text('TLS（常用）'), 'ssl' => 'SSL', '' => admin_text('不加密')] as $val => $label)
                            <option value="{{ $val }}" @selected(old('mail_encryption', $s['mail_encryption'] ?? 'tls') === (string) $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <label for="mail_username">{{ admin_text('登录账号') }}</label>
            <input id="mail_username" type="text" name="mail_username" value="{{ old('mail_username', $s['mail_username'] ?? '') }}">
            <label for="mail_password">{{ admin_text('密码') }}</label>
            <input id="mail_password" type="password" name="mail_password" value="" autocomplete="new-password" placeholder="{{ ! empty($s['mail_password']) ? admin_text('已保存，留空不改') : '' }}">
            <label for="mail_from_address">{{ admin_text('发件人邮箱') }}</label>
            <input id="mail_from_address" type="email" name="mail_from_address" value="{{ old('mail_from_address', $s['mail_from_address'] ?? '') }}">
            <label for="mail_from_name">{{ admin_text('发件人名称') }}</label>
            <input id="mail_from_name" type="text" name="mail_from_name" value="{{ old('mail_from_name', $s['mail_from_name'] ?? '') }}">

        </div>
        @endif

        @if($tab === 'api')
        <div class="settings-pane active" data-pane="api">
            <h3>{{ admin_text('加速与接口') }}</h3>
            <label for="cdn_url">{{ admin_text('图片走这个域名') }}</label>
            <input id="cdn_url" type="url" name="cdn_url" value="{{ old('cdn_url', $s['cdn_url'] ?? '') }}" placeholder="https://cdn.example.com">
            <p class="muted field-hint">{{ admin_text('附件会改到这个域名。留空则用本站地址。不要填后台地址。') }}</p>
            <label class="inline">
                <input type="checkbox" name="api_enable" value="1" @checked(old('api_enable', $s['api_enable'] ?? '0') == '1')>
                {{ admin_text('读内容需要密钥') }}
            </label>
            <label for="api_token">{{ admin_text('接口密钥') }}</label>
            <input id="api_token" type="text" name="api_token" value="{{ old('api_token', $s['api_token'] ?? '') }}" placeholder="{{ admin_text('一串不容易猜的字符') }}">
            <p class="muted field-hint">{{ admin_text('勾选「需要密钥」时必填。关掉则谁都能读公开内容。') }}</p>
            <details class="settings-details">
                <summary>{{ admin_text('有哪些接口') }}</summary>
                <p class="muted field-hint">{{ admin_text('读：站点配置、栏目、文章、搜索、标签、推荐位。写：表单提交、评论（带密钥可免验证码）。地址在网站根路径下的 /api。') }}</p>
            </details>

        </div>
        @endif

        @if($tab === 'seo')
        <div class="settings-pane active" data-pane="seo">
            <h3>{{ admin_text('搜索引擎') }}</h3>
            <label for="robots_extra">{{ admin_text('robots.txt 补充规则') }}</label>
            <textarea id="robots_extra" name="robots_extra" rows="5" placeholder="Disallow: /private">{{ old('robots_extra', $s['robots_extra'] ?? '') }}</textarea>
            <p class="muted field-hint">{!! str_replace(':open', auth('admin')->user()?->canPerm('sitemap') ? ' (<a href="'.e(route('admin.sitemap.edit')).'">'.e(admin_text('打开')).'</a>)' : '', e(admin_text('写在默认规则后面。关站时整站仍是 Disallow: /。后台、会员、安装、预览已经禁止抓取。前台地址 /robots.txt。收录范围在侧栏「:ops → :tools → :sitemap」。:open', ['ops' => admin_t('nav.ops'), 'tools' => admin_t('nav.ops_tools'), 'sitemap' => admin_t('nav.sitemap')]))) !!}</p>

            <h3>{{ admin_text('统计与内链') }}</h3>
            <label for="analytics_code">{{ admin_text('统计代码') }}</label>
            <textarea id="analytics_code" name="analytics_code" rows="5" placeholder="{{ admin_text('把统计平台给的代码贴在这里') }}">{{ old('analytics_code', $s['analytics_code'] ?? '') }}</textarea>
            <p class="muted field-hint">{{ admin_text('会插到前台页面底部，如百度统计、Google Analytics。') }}</p>
            <label class="inline">
                <input type="checkbox" name="tag_autolink" value="1" @checked(old('tag_autolink', $s['tag_autolink'] ?? '1') != '0')>
                {{ admin_text('正文里自动给标签加链接') }}
            </label>
            <p class="muted field-hint">{{ admin_text('文章里出现标签名时，点过去会打开标签页。') }}</p>

            <details class="settings-details">
                <summary>{{ admin_text('后台选点位置') }}</summary>
                <p class="muted field-hint">{{ admin_text('填内容选地点时，地图打开的默认位置。一般不用改。') }}</p>
                <div class="settings-two">
                    <div>
                        <label for="map_default_lng">{{ admin_text('经度') }}</label>
                        <input id="map_default_lng" type="text" name="map_default_lng" value="{{ old('map_default_lng', $s['map_default_lng'] ?? '116.397') }}">
                    </div>
                    <div>
                        <label for="map_default_lat">{{ admin_text('纬度') }}</label>
                        <input id="map_default_lat" type="text" name="map_default_lat" value="{{ old('map_default_lat', $s['map_default_lat'] ?? '39.908') }}">
                    </div>
                </div>
            </details>
        </div>
        @endif

        <div class="form-actions settings-save">
            <button class="btn" type="submit">{{ admin_text('保存设置') }} · {{ $tabs[$tab] }}</button>
        </div>
    </form>
    @if(auth('admin')->user()?->isSuperAdmin())
        <p class="muted"><a href="{{ route('admin.settings.maintenance') }}">{{ admin_text('系统维护') }}</a>{{ admin_text('：备份与恢复在这里单独做。') }}</p>
    @endif
@endsection
@push('scripts')
<script src="{{ asset('assets/form-guard.js') }}" defer></script>
<script>
(function () {
    document.querySelectorAll('[data-file-preview]').forEach(function (root) {
        var input = root.querySelector('[data-file-input]');
        var img = root.querySelector('img');
        var empty = root.querySelector('.settings-file-empty');
        var thumb = root.querySelector('.settings-file-thumb');
        var clear = root.querySelector('[data-file-clear]');
        if (!input || !img || !empty || !thumb) return;
        var objectUrl = '';
        var saved = (img.getAttribute('data-saved-src') || '').trim();
        var show = function (url) {
            if (objectUrl && objectUrl !== url) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = '';
            }
            if (!url) {
                img.removeAttribute('src');
                img.hidden = true;
                empty.hidden = false;
                thumb.classList.add('is-empty');
                thumb.style.backgroundImage = 'none';
                return;
            }
            img.hidden = false;
            empty.hidden = true;
            thumb.classList.remove('is-empty');
            thumb.style.backgroundImage = 'url("' + String(url).replace(/"/g, '\\"') + '")';
            if (img.getAttribute('src') !== url) {
                img.src = url;
            }
        };
        var nameEl = root.querySelector('[data-file-name]');
        var pick = root.querySelector('[data-file-pick]');
        var emptyName = nameEl ? (nameEl.getAttribute('data-file-empty') || '') : '';
        var showName = function (text) {
            if (nameEl) nameEl.textContent = text || emptyName;
        };
        if (pick) {
            pick.addEventListener('click', function () {
                input.click();
            });
        }
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (clear) clear.checked = false;
            if (!file) {
                show(saved);
                showName('');
                return;
            }
            objectUrl = URL.createObjectURL(file);
            show(objectUrl);
            showName(file.name);
        });
        if (clear) {
            clear.addEventListener('change', function () {
                if (clear.checked) {
                    input.value = '';
                    show('');
                    showName('');
                } else {
                    show(saved);
                    showName('');
                }
            });
        }
    });
})();
</script>
@endpush
