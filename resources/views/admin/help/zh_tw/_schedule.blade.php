@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $siteRoot = str_replace('\\', '/', rtrim(base_path(), '/\\'));
@endphp
<h2>定時任務和隊列</h2>
<p class="muted">採集、統計清理不會自己跑，要靠系統每分鐘叫醒 Laravel。PHP / nginx 見「<a href="{{ $urls['env'] ?? '#' }}">環境</a>」。採集源在{!! $hl('collectors', '採集') !!}裡配，間隔到了才會真正拉取。</p>

<h3>調度會做什麼</h3>
<table class="help-doc">
    <thead>
        <tr><th>任務</th><th>何時</th><th>說明</th></tr>
    </thead>
    <tbody>
        <tr><td>採集</td><td>每分鐘問一次</td><td>各採集源按自己的「間隔（分鐘）」決定這次要不要抓</td></tr>
        <tr><td>統計清理</td><td>每天 03:20</td><td>預設刪掉 90 天以前的訪問記錄</td></tr>
    </tbody>
</table>
<p class="hint">本機 <code>php artisan serve</code> 或 Docker 試用可以不配。正式站至少要有下面「純定時」那一行 cron，否則後臺點「立即採集」才能跑。寶塔用戶：週期選「N分鐘」填 1，腳本框不要貼 <code>* * * * *</code>，命令用 <code>bin/php</code> 不要用 <code>php-fpm</code>。圖解在 <a href="{{ $urls['baota'] ?? route('admin.help', ['topic' => 'baota']) }}">寶塔</a>。</p>

<h3>怎麼選模式</h3>
<table class="help-doc">
    <thead>
        <tr><th>CMS_COLLECT_MODE</th><th>行為</th><th>還要什麼進程</th></tr>
    </thead>
    <tbody>
        <tr><td><code>schedule</code>（預設）</td><td>定時到了就在當前進程裡抓</td><td>只要 cron 每分鐘跑 <code>schedule:run</code></td></tr>
        <tr><td><code>queue</code></td><td>定時只往隊列丟任務</td><td>cron + 常駐 <code>queue:work</code>（多站可共用）</td></tr>
        <tr><td><code>off</code></td><td>不自動跑</td><td>只靠後臺按鈕或手動命令</td></tr>
    </tbody>
</table>
<p class="muted"><code>CMS_STAT_PRUNE_MODE</code> 同樣三個值。改 <code>.env</code> 後執行 <code>php artisan config:clear</code>。</p>

<h3>推薦：上線最小配置</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>CMS_SCHEDULE_ENABLED=true
CMS_COLLECT_MODE=schedule
CMS_STAT_PRUNE_MODE=schedule</code></pre>
</div>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>* * * * * cd {{ $siteRoot }} && php artisan schedule:run >> /dev/null 2>&amp;1</code></pre>
</div>
<p class="hint">下面命令裡的路徑就是當前這個安裝的目錄。Linux 用 crontab；Windows 用任務計劃程式，每分鐘執行同一條 <code>php artisan schedule:run</code>。</p>

<h3>採集量大時用隊列</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>CMS_SCHEDULE_ENABLED=true
CMS_COLLECT_MODE=queue
CMS_COLLECT_QUEUE=cms
CMS_STAT_PRUNE_MODE=queue
CMS_STAT_PRUNE_QUEUE=cms
QUEUE_CONNECTION=database</code></pre>
</div>
<p class="muted"><code>QUEUE_CONNECTION</code> 不要用 <code>sync</code>，否則還是當場執行，等於沒隊列。可用 <code>database</code> 或 Redis。</p>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>* * * * * cd {{ $siteRoot }} && php artisan schedule:run >> /dev/null 2>&amp;1
php artisan queue:work --queue=cms --sleep=1 --tries=1</code></pre>
</div>
<p class="hint">第二行要常駐（systemd / supervisor）。掛了採集和清理都不會跑。</p>

<h3>不用 Laravel 調度、自己寫 cron</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>CMS_SCHEDULE_ENABLED=false</code></pre>
</div>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>* * * * * cd {{ $siteRoot }} && php artisan cms:collect --sync
20 3 * * * cd {{ $siteRoot }} && php artisan cms:stat-prune --days=90 --sync</code></pre>
</div>
<p class="muted">隊列版把 <code>--sync</code> 換成 <code>--queue</code>，並同樣要 <code>queue:work --queue=cms</code>。</p>

<h3>手動命令</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>php artisan cms:collect
php artisan cms:collect --force
php artisan cms:collect --id=1 --force
php artisan cms:collect --queue
php artisan cms:collect --sync
php artisan cms:stat-prune --days=90</code></pre>
</div>
<p class="hint">Docker 試用可 <code>docker compose exec app php artisan cms:collect</code>。演示環境一般不必配 cron。</p>
