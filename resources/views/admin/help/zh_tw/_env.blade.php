@php
    $siteId = (int) ($siteId ?? 1);
    $siteRoot = str_replace('\\', '/', rtrim(base_path(), '/\\'));
    $compact = (bool) ($compact ?? false);
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<div class="deploy-guide">
    @unless($compact)
        <h2>上線要準備什麼</h2>
        <p class="muted">本機 <code>php artisan serve</code> 只適合試用。正式站點需要 PHP、資料庫，以及 nginx（或同類）把請求指到 <code>public</code> 目錄。</p>
    @endunless

    <h3>PHP 環境</h3>
    <p class="muted">需要 PHP <strong>8.4 或更高</strong>，並打開這些擴展：<code>pdo</code>、<code>mbstring</code>、<code>openssl</code>、<code>tokenizer</code>、<code>xml</code>、<code>ctype</code>、<code>json</code>、<code>fileinfo</code>、<code>curl</code>。用 MySQL 再開 <code>pdo_mysql</code>；用 SQLite 再開 <code>pdo_sqlite</code>。</p>
    <p class="muted">圖片水印和縮略圖需要 <code>gd</code> 或 <code>imagick</code>，沒有也能裝，只是這兩項不可用。</p>
    <p class="muted">建議在 <code>php.ini</code> 裡：</p>
    <div class="code">
        <button type="button" class="copy" data-copy>複製</button>
        <pre><code>memory_limit = 256M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 60
date.timezone = Asia/Shanghai</code></pre>
    </div>
    <p class="hint">改完後重啟 php-fpm 或網站服務。目錄 <code>storage</code> 和 <code>bootstrap/cache</code> 必須可寫。寶塔若禁用了 <code>exec</code> / <code>symlink</code>，不必再跑 <code>php artisan storage:link</code>，上傳檔案由站點自己從 <code>/storage</code> 讀出。要做成真正軟鏈（圖片不走 PHP），在寶塔終端執行 <code>ln -s {{ $siteRoot }}/storage/app/public {{ $siteRoot }}/public/storage</code>。</p>

    <h3>MySQL</h3>
    <p class="muted">正式站點建議 MySQL 5.7 / 8.0 或 MariaDB 10.3+，字符集用 <code>utf8mb4</code>。先建空庫，再把賬號寫進 <code>.env</code>。</p>
    <div class="code">
        <button type="button" class="copy" data-copy>複製</button>
        <pre><code>CREATE DATABASE cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cms'@'127.0.0.1' IDENTIFIED BY '請換成強密碼';
GRANT ALL ON cms.* TO 'cms'@'127.0.0.1';
FLUSH PRIVILEGES;</code></pre>
    </div>
    <div class="code">
        <button type="button" class="copy" data-copy>複製</button>
        <pre><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms
DB_USERNAME=cms
DB_PASSWORD=請換成強密碼</code></pre>
    </div>
    <p class="hint">試用可以繼續用 SQLite（<code>DB_CONNECTION=sqlite</code>），不用另裝資料庫。改完 <code>.env</code> 後執行 <code>php artisan config:clear</code>。</p>

    <h3>nginx</h3>
    <p class="muted">網站根目錄必須是項目裡的 <code>public</code>，不要指到項目根。下面的路徑就是當前這個安裝的目錄。<code>php8.4-fpm.sock</code> 換成本機 PHP-FPM 套接字。</p>
    <div class="code">
        <button type="button" class="copy" data-copy>複製</button>
        <pre><code>server {
    listen 80;
    server_name www.example.com;
    root {{ $siteRoot }}/public;
    index index.php;
    charset utf-8;
    client_max_body_size 32m;

    location / {
        try_files /html/site_{{ $siteId }}$uri/index.html /html/site_{{ $siteId }}$uri $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}</code></pre>
    </div>
    <p class="hint">第一行 <code>try_files</code> 會優先讀後臺生成的磁盤靜態頁。沒有檔案時再交給 PHP。靜態頁目錄是 <code>site_{{ $siteId }}</code>。HTTPS 請在前面加證書或由面板處理。</p>
    <p class="muted">還要在 <code>.env</code> 寫上 <code>APP_URL=https://www.example.com</code>，<code>APP_ENV=production</code>，<code>APP_DEBUG=false</code>。若站點在反向代理後面，再設 <code>TRUST_PROXIES=*</code>。</p>

    <h3>搜索引擎 robots.txt</h3>
    <p class="muted">前臺地址是 <code>/robots.txt</code>。預設允許抓首頁，禁止 <code>/admin</code>、<code>/install</code>、<code>/member</code>、<code>/preview</code>，並帶上各語言的 sitemap。關站時整站是 <code>Disallow: /</code>。</p>
    <p class="muted">還要擋住某幾段路徑時，到{!! $settingsLink('seo', '設定 → 搜尋與統計') !!}寫「robots.txt 補充規則」，例如：</p>
    <div class="code">
        <button type="button" class="copy" data-copy>複製</button>
        <pre><code>Disallow: /private
Disallow: /drafts</code></pre>
    </div>
    <p class="hint">不要寫 HTML 標籤。大約最多 40 行、每行 200 字。改完打開前臺 <code>/robots.txt</code> 核對。這和頁面裡的 <code>&lt;meta name="robots"&gt;</code> 不是一回事。收錄範圍裝好後在後臺「運營 → 營運工具 → 地圖」。</p>

    <h3>圖片放到對象存儲</h3>
    <p class="muted">預設傳到本機 <code>storage/app/public</code>。有軟鏈時 nginx 直接吐檔案；沒有軟鏈時走站點 <code>/storage</code> 路由，不依賴寶塔放開 <code>exec</code>。只換訪問域名時，在{!! $settingsLink('api', '設定 → 加速與介面') !!}填「圖片走這個域名」，不必改盤。</p>
    <p class="muted">要用阿裡雲 OSS / 騰訊雲 COS / AWS S3 存檔案：先 <code>composer require league/flysystem-aws-s3-v3</code>，再在 <code>.env</code> 寫：</p>
    <div class="code">
        <button type="button" class="copy" data-copy>複製</button>
        <pre><code>CMS_MEDIA_DISK=s3
AWS_ACCESS_KEY_ID=你的 Key
AWS_SECRET_ACCESS_KEY=你的 Secret
AWS_DEFAULT_REGION=oss-cn-hangzhou
AWS_BUCKET=桶名
AWS_URL=https://桶名.oss-cn-hangzhou.aliyuncs.com
AWS_ENDPOINT=https://oss-cn-hangzhou.aliyuncs.com
AWS_USE_PATH_STYLE_ENDPOINT=false</code></pre>
    </div>
    <p class="hint">對象存儲上不跑本機 GD 水印。需要水印時先在本機盤處理，或用 CDN 的圖片處理。改完執行 <code>php artisan config:clear</code>。</p>

    <h3>nginx 反代到 Docker</h3>
    <p class="muted">容器只映射 8000 時，浏覽器直連看到的是 Docker 網關 IP。前面加 nginx，把真實客戶端 IP 傳進去：</p>
    <div class="code">
        <button type="button" class="copy" data-copy>複製</button>
        <pre><code>location / {
    proxy_pass http://127.0.0.1:8000;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Host $host;
    proxy_set_header X-Forwarded-Port $server_port;
}</code></pre>
    </div>
    <p class="hint">容器環境變量裡也要有 <code>TRUST_PROXIES=*</code>（本倉庫 <code>docker-compose.yml</code> 已寫上）。只映射端口、前面沒有 nginx 時，程式拿不到訪客公網 IP。</p>
    @if(!empty($scheduleUrl))
        <p class="hint">採集和統計清理要每分鐘叫醒 Laravel，見「<a href="{{ $scheduleUrl }}">定時</a>」。</p>
    @endif
</div>
