<div class="card guide">
    <div class="pane">
        <h2>用 Docker 安裝</h2>
        <p class="muted">適合本機已裝 Docker Desktop。一條命令會裝依賴、建表，並導入演示數據。</p>

        <h3>1. 進入項目目錄後啟動</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>複製</button>
            <pre><code>docker compose up --build</code></pre>
        </div>
        <p class="hint">第一次會編譯鏡像，需要幾分鐘。看到 <code>Development Server</code> 就好了。容器裡每分鐘會自己執行計劃任務，不用再配系統定時。</p>

        <h3>2. 打開網站</h3>
        <p class="muted">前臺 <a href="http://127.0.0.1:8000/">http://127.0.0.1:8000/</a><br>後臺 <a href="http://127.0.0.1:8000/admin/login">http://127.0.0.1:8000/admin/login</a></p>

        <h3>3. 演示賬號（僅本地）</h3>
        <table class="checks">
            <tr><td>超級管理員</td><td><code>admin@example.com</code> / <code>Admin@12345</code></td></tr>
            <tr><td>編輯</td><td><code>editor@example.com</code> / <code>Editor@12345</code></td></tr>
            <tr><td>會員</td><td><code>member@example.com</code> / <code>Member@12345</code></td></tr>
        </table>
        <p class="hint">上線前必須改掉這些密碼。不要演示數據時，用網頁安裝或 Laravel 命令行，不要加 <code>--demo</code>。</p>

        <h3>常用命令</h3>
        <div class="code">
            <button type="button" class="copy" data-copy>複製</button>
            <pre><code>docker compose down
docker compose exec app php artisan test</code></pre>
        </div>
        <p class="hint">容器工作目錄是 <code>/var/www/html</code>，項目檔案會掛進去。</p>
    </div>
</div>
