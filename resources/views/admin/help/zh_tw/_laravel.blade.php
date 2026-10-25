<div class="card guide">
    <div class="pane">
        @php
            $deployUrl = $deployUrl ?? route('install', ['way' => 'deploy']);
            $webInstallUrl = $webInstallUrl ?? route('install', ['way' => 'web']);
        @endphp
        <h2>用 Laravel 命令行安裝</h2>
        <p class="muted">適合本機已有 PHP 8.4+ 和 Composer 的情況。預設用 SQLite，不用另裝資料庫。</p>

        <h3>一條命令</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>複製</button>
            <pre><code>bash install.sh</code></pre>
        </div>
        <p class="hint">Windows：<code>powershell -ExecutionPolicy Bypass -File .\install.ps1</code>。不寫密碼時，郵箱是 <code>admin@example.com</code>，密碼是 <code>Admin@12345</code>。上線後請改掉。自己傳密碼：<code>bash install.sh '你的密碼'</code>，至少 8 位。用寶塔的話先看 <a href="{{ route('install', ['way' => 'baota']) }}">寶塔圖解</a>。</p>

        <h3>1. 準備代碼</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>複製</button>
            <pre><code>composer install
cp .env.example .env
php artisan key:generate</code></pre>
        </div>
        <p class="hint">Windows 把第二行換成 <code>copy .env.example .env</code>。</p>

            <h3>2. 資料庫（可選）</h3>
        <p class="muted">預設已經是 SQLite。要用 MySQL 5.7+ / 8.0，先建空庫（utf8mb4），再改 <code>.env</code>：</p>
        <div class="code">
            <button type="button" class="copy" data-copy>複製</button>
            <pre><code>DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms
DB_USERNAME=cms
DB_PASSWORD=請換成強密碼</code></pre>
        </div>
        <p class="hint">建庫示例：<code>CREATE DATABASE cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code> PHP 需打開 <code>pdo_mysql</code>。上線 nginx 見 <a href="{{ $deployUrl }}">環境</a>。</p>

        <h3>3. 安裝</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>複製</button>
            <pre><code>php artisan cms:install --email=you@example.com --password=YourPass#1</code></pre>
        </div>
        <p class="hint">加上 <code>--demo</code> 會導入示例欄目和文章。還可以寫 <code>--site=我的網站 --name=管理員</code>。</p>

        <h3>4. 啟動</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>複製</button>
            <pre><code>php artisan serve</code></pre>
        </div>
        <p class="muted">浏覽器打開 <a href="{{ url('/') }}">{{ url('/') }}</a>，後臺 <a href="{{ url('/admin/login') }}">{{ url('/admin/login') }}</a>，用上面填的郵箱登錄。</p>
        <p class="hint">也可以不跑 <code>cms:install</code>，執行前兩步後打開 <a href="{{ $webInstallUrl }}">網頁安裝</a> 用浏覽器點完。</p>
        <p class="hint">二次開發不要改核心檔案。文章保存、評論、表單、換主題會發出 <code>App\Events\*</code>，寫法見後臺說明「模板」。</p>
    </div>
</div>
