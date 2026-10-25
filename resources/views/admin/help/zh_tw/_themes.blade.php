@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<p class="muted">主題就是一套前臺外觀：Blade 檔案，加上它聲明要用的模型。我沒有做應用市場，都在本機 {!! $hl('themes', '主題') !!} 頁切換。改檔案和標籤見「<a href="{{ $urls['theme'] ?? '#' }}">模板</a>」。</p>

<div class="help-jumps">
    @if(!empty($urls['themes']))
        <a href="{{ $urls['themes'] }}">打開主題頁</a>
    @endif
    @if(!empty($urls['cache']))
        <a href="{{ $urls['cache'] }}">清緩存</a>
    @endif
    <a href="{{ $urls['theme'] ?? '#' }}">改模板檔案</a>
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">看前臺</a>
</div>

<h3>怎麼換到本站</h3>
<ol class="help-steps">
    <li>打開{!! $hl('themes', '主題') !!}。卡片上「當前使用」就是正在用的。點「配置」進這一套的多語言、模型和自定義參數。</li>
    <li>點預覽圖看長什麼樣。缺首頁的不能用於本站（沒有 <code>index/index.blade.php</code>）。</li>
    <li>點「用於本站」：會把這套外觀設為當前主題，並<strong>補上缺少的內容模型</strong>和<strong>空欄目</strong>。已有模型字段、已有欄目和文章都不會改、不會刪。</li>
    <li>當前這套還缺模型時，按鈕是「補上模型」，只補缺的，不換外觀。</li>
    <li>前臺還是舊樣子，到{!! $hl('cache', '緩存') !!}清空。</li>
</ol>
<p class="hint">空欄目補上後還沒有文章，需要自己寫。體育主題只作公開資訊/目錄演示，請合法合規使用。</p>

<h3>怎麼加一套</h3>
<table class="help-doc">
    <thead>
        <tr><th>做法</th><th>說明</th></tr>
    </thead>
    <tbody>
        <tr><td>複製一套</td><td>從現有完整主題拷資料夾。只改新目錄，自帶主題不會被覆蓋。</td></tr>
        <tr><td>上傳 zip</td><td>包裡要有 <code>index/index.blade.php</code>，可帶一層目錄名當標識。不要帶可執行 PHP。</td></tr>
        <tr><td>丟資料夾</td><td>放到 <code>resources/views/themes/標識/</code>，刷新主題頁即出現。</td></tr>
        <tr><td>命令行</td><td><code>php artisan cms:theme-make brand --from=enterprise --name=品牌站</code></td></tr>
    </tbody>
</table>
<p class="muted">自帶主題不能刪。自己加的可以「去掉」（會刪資料夾），正在用的不能去掉。標識只能小寫字母數字和橫線。</p>

<h3>自帶主題幹什麼用</h3>
<table class="help-doc">
    <thead>
        <tr><th>主題</th><th>適合</th><th>會用到的模型</th></tr>
    </thead>
    <tbody>
        <tr><td>企業</td><td>公司官網</td><td>文章、單頁、產品、招聘</td></tr>
        <tr><td>博客</td><td>簡單博客</td><td>文章</td></tr>
        <tr><td>下載</td><td>資源下載</td><td>下載</td></tr>
        <tr><td>導航</td><td>網址導航</td><td>鏈接</td></tr>
        <tr><td>源碼</td><td>源碼站</td><td>源碼</td></tr>
        <tr><td>漫畫</td><td>漫畫連載</td><td>漫畫、章節</td></tr>
        <tr><td>體育</td><td>資訊/信號目錄</td><td>信號</td></tr>
        <tr><td>移動</td><td>手機皮膚</td><td>與企業類似，可作手機外觀</td></tr>
    </tbody>
</table>
<p class="muted">點「用於本站」時按 <code>theme.json</code> 裡的 <code>models</code> / <code>categories</code> 補缺（配置頁勾選的模型會覆蓋 json）。欄目標題可寫成 <code>{"zh_cn":"新聞中心","en":"News"}</code>。卡片上「多語言」「自適應」也可在配置頁勾選。自定義參數寫在 <code>config</code>，前臺 <code>cms_theme('鍵')</code>。</p>

<h3>站名、Logo、手機外觀</h3>
<p class="muted">{!! $hl('settings', '設定') !!}裡改網站名稱。{!! $settingsLink('look', '設定 → 網站外觀') !!}可上傳 Logo / Favicon、主色、頁腳、ICP。留空則用主題自帶配色。</p>
<p class="muted">手機要另一套皮：在{!! $settingsLink('look', '設定 → 網站外觀') !!}裡選「手機外觀」（常用 <code>mobile</code>），可再填手機域名，或按手機浏覽器自動切。</p>

<h3>主題相關問題</h3>
<div class="help-faq">
    <details>
        <summary>用於本站會不會清掉現在的文章？</summary>
        <p>不會。只補<strong>沒有的</strong>模型和空欄目。已有字段、欄目、文章原樣保留。換皮後字段對不上的，前臺可能顯示不全，需要自己改欄目模型或模板。</p>
    </details>
    <details>
        <summary>按鈕是灰的 / 顯示缺首頁</summary>
        <p>這套沒有 <code>index/index.blade.php</code>。複製或上傳時包不完整就會這樣。自帶主題不應出現。</p>
    </details>
    <details>
        <summary>換了主題前臺沒變</summary>
        <p>到{!! $hl('cache', '緩存') !!}清空。磁盤靜態頁若開着，也要重新生成。</p>
    </details>
    <details>
        <summary>複製後改哪</summary>
        <p>只改新資料夾裡的 Blade / CSS / <code>theme.json</code>。改完刷新主題頁。預覽圖放 <code>preview.png</code>。標籤怎麼寫見「<a href="{{ $urls['tags'] ?? '#' }}">標籤</a>」。</p>
    </details>
</div>
