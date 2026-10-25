@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<p class="muted">LeafCMS 是我用 Laravel 寫的建站程式，協議 MIT。前臺樣子由<strong>主題</strong>決定，文章字段由<strong>內容模型</strong>決定。沒有應用商店，也不會做在線升級。這份說明和倉庫 README 說的是同一件事。</p>
<p class="muted">側欄每一項見「<a href="{{ $urls['helpAdmin'] ?? '#' }}">後臺</a>」，換外觀見「<a href="{{ $urls['helpThemes'] ?? '#' }}">主題</a>」，改 Blade 見「<a href="{{ $urls['theme'] ?? '#' }}">模板</a>」，取數據見「<a href="{{ $urls['tags'] ?? '#' }}">標籤</a>」。空站不要一上來改模板，先讓網站能打開、能發出一篇文章。</p>

<div class="help-jumps">
    @if(!empty($urls['settings']))
        <a href="{{ $urls['settings'] }}">改站名</a>
    @endif
    @if(!empty($urls['themes']))
        <a href="{{ $urls['themes'] }}">換主題</a>
    @endif
    @if(!empty($urls['categories']))
        <a href="{{ $urls['categories'] }}">建欄目</a>
    @endif
    @if(!empty($urls['entries']))
        <a href="{{ $urls['entries'] }}">寫內容</a>
    @endif
    @if(!empty($urls['cache']))
        <a href="{{ $urls['cache'] }}">清緩存</a>
    @endif
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">看前臺</a>
</div>

<h3>第一次建議按這個順序</h3>
<ol class="help-steps">
    <li>{!! $hl('settings', '設定') !!} 裡改網站名稱、一句話介紹。</li>
    <li>{!! $hl('themes', '主題') !!} 選一套外觀，點「用於本站」。缺的模型會自動補上，已有文章不會清掉。</li>
    <li>{!! $hl('categories', '欄目') !!} 建頻道（新聞、產品、關於我們）。欄目要勾模型，列表頁才知道用哪套字段。</li>
    <li>{!! $hl('entries', '內容') !!} 寫文章。有發布權的人點發布，編輯預設只能存草稿或待審。</li>
    <li>打開前臺看效果。換主題後還是舊樣子，到{!! $hl('cache', '緩存') !!}清空。</li>
</ol>
<p class="hint">安裝時勾了演示數據，前臺已經有欄目和文章，改文案即可。空站則從欄目開始建。側欄沒有的項是當前賬號沒權限。</p>

<h3>側欄對應什麼</h3>
<table class="help-doc">
    <thead>
        <tr><th>位置</th><th>做什麼</th></tr>
    </thead>
    <tbody>
        <tr><td>內容</td><td>{!! $hl('categories', '欄目') !!}、{!! $hl('entries', '文章') !!}、{!! $hl('entryTags', '標籤') !!}、{!! $hl('media', '媒體') !!}、{!! $hl('submissions', '留言') !!}、{!! $hl('comments', '評論') !!}。「內容設定」裡有{!! $hl('flags', '推薦') !!}、{!! $hl('recommends', '推薦位') !!}、{!! $hl('models', '模型') !!}、{!! $hl('menus', '導航') !!}、{!! $hl('forms', '表單') !!}、{!! $hl('recycle', '回收站') !!}。</td></tr>
        <tr><td>運營</td><td>{!! $hl('members', '會員') !!}、{!! $hl('collectors', '採集') !!}、{!! $hl('cache', '緩存') !!}（頁面緩存和磁盤靜態頁）。「營運工具」裡有{!! $hl('friendLinks', '友鏈') !!}、{!! $hl('sitemap', '地圖') !!}、{!! $hl('redirects', '跳轉') !!}、{!! $hl('groups', '分組') !!}。</td></tr>
        <tr><td>系統</td><td>{!! $hl('settings', '設定') !!}、{!! $hl('themes', '主題') !!}、語言、日志、管理員、權限，以及本頁說明。說明不跟權限走，所以留在系統最底下。</td></tr>
    </tbody>
</table>

<h3>欄目和文章</h3>
<p class="muted">欄目類型常見三種：<strong>列表</strong>出文檔列表，<strong>單頁</strong>只顯示一篇（如關於我們），<strong>外鏈</strong>點過去是別的網址。一篇文章有一個主欄目（決定網址），也可以勾附加欄目，出現在多個列表裡。</p>
<p class="muted">模型決定字段。文章模型一般是標題、封面、正文；產品會多價格等。{!! $hl('models', '模型') !!}可以複製、導出 JSON、再導入，不會刪已有文檔。</p>
<p class="muted">定時發布：發布時間沒到，前臺是 404。草稿可生成預覽鏈接，不計入浏覽、不進緩存。</p>

<h3>導航、推薦位、表單</h3>
<p class="muted">欄目勾「菜單」後，主題裡 <code>@@cmsMenu</code> 沒配導航時會拿這些欄目當頂欄。要精確控制順序，用{!! $hl('menus', '導航') !!}建 header 菜單。</p>
<p class="muted">首頁 Banner、最新新聞這類區塊用{!! $hl('recommends', '推薦位') !!}，標識要和模板裡的 <code>name</code> 對上，例如 <code>home_banner</code>。手填條目點「選擇」從媒體庫挑圖，保存前能看到預覽；模板裡用 <code>cms_media($item-&gt;image)</code> 輸出。</p>
<p class="muted">首頁友情鏈接 / 合作夥伴走側欄「運營 → 營運工具 → {!! $hl('friendLinks', '友鏈') !!}」，數據存在推薦位 <code>partners</code>（沒有則用 <code>friend_links</code>）。這和導航站主題裡的「鏈接」模型不是一回事：那個是網址目錄。</p>
<p class="muted">聯繫表單在{!! $hl('forms', '表單') !!}裡建，前臺用 <code>@@cmsForm</code> 按標識輸出。提交進「留言」。評論預設待審核，可在{!! $settingsLink('interact', '設定 → 評論規則') !!}裡開關。搜索引擎用的地址清單在「運營 → 營運工具 → {!! $hl('sitemap', '地圖') !!}」。</p>

<h3>會員</h3>
<p class="muted">前臺 <code>/member</code> 可注冊登錄。模型勾「允許會員投稿」且分組有投稿權限後，會員能寫稿，後臺審過再發布。設置裡可開評論、游客評論。</p>

<h3>備份、緩存</h3>
<p class="muted">備份還原在「系統 → 系統維護」，僅超級管理員可用；robots.txt 補充規則在{!! $settingsLink('seo', '設定 → 搜尋與統計') !!}。備份也可用命令 <code>php artisan cms:backup</code>。緩存、靜態頁生成都在{!! $hl('cache', '緩存') !!}，nginx 和對象存儲見「<a href="{{ $urls['env'] ?? '#' }}">環境</a>」。</p>

<h3>常見問題</h3>
<div class="help-faq">
    <details>
        <summary>換了主題，前臺還是舊樣子</summary>
        <p>頁面緩存還在。到{!! $hl('cache', '緩存') !!}點「清空緩存」，或執行 <code>php artisan cms:html-clear</code>。</p>
    </details>
    <details>
        <summary>寫了文章前臺看不到</summary>
        <p>確認已<strong>發布</strong>（不是草稿/待審），發布時間已到，且掛在對的欄目。編輯賬號預設沒有發布權，需要超管或有「發布內容」權限的人點發布。</p>
    </details>
    <details>
        <summary>圖片或上傳檔案 404</summary>
        <p>項目根執行過 <code>php artisan storage:link</code>，且 <code>storage</code>、<code>bootstrap/cache</code> 可寫。配了 CDN 時檢查「設定 → 加速與介面」裡的資源域名。若 <code>.env</code> 寫了 <code>CMS_MEDIA_DISK=s3</code>，檔案不在本機 <code>public/storage</code>，要核對桶名和 <code>AWS_URL</code>。</p>
    </details>
    <details>
        <summary>推薦位、表單是空的</summary>
        <p>後臺標識要和模板參數一致：推薦位 <code>name</code>（如 <code>home_banner</code>），表單 <code>slug</code>（如 <code>contact</code>）。沒啟用或沒條目也不會顯示。手填推薦位有圖卻前臺空白時，模板請用 <code>cms_media($item-&gt;image)</code>，不要寫死本機路徑。</p>
    </details>
    <details>
        <summary>不想讓搜索引擎抓某幾頁</summary>
        <p>打開前臺 <code>/robots.txt</code> 先看預設規則（後臺、會員、安裝、預覽已經禁止）。再在{!! $settingsLink('seo', '設定 → 搜尋與統計') !!}寫「robots.txt 補充規則」，例如 <code>Disallow: /private</code>。關站時整站仍是 <code>Disallow: /</code>。不要寫 HTML 標籤。收錄範圍在「運營 → 營運工具 → {!! $hl('sitemap', '地圖') !!}」。</p>
    </details>
    <details>
        <summary>站點地圖在哪</summary>
        <p>搜索引擎看 <code>/sitemap.xml</code>（英文站是 <code>/en/sitemap.xml</code>）。給人看的目錄是 <code>/sitemap</code>，可在後臺關掉，頁腳鏈接會一起消失。勾選欄目 / 文章 / 標籤、加補充地址，都在側欄「運營 → 營運工具 → {!! $hl('sitemap', '地圖') !!}」。會員才能看的文章、沒有公開文章的標籤不會列入。欄目標識不要寫成 sitemap。</p>
    </details>
    <details>
        <summary>想重新跑安裝向導</summary>
        <p>刪掉 <code>storage/app/install.lock</code> 後再打開 <code>/install</code>。已有數據不會自動清空，正式站不要這樣做。</p>
    </details>
    <details>
        <summary>上線打開是目錄或 404</summary>
        <p>網站根必須指到項目的 <code>public</code>，不要指到項目根。完整 nginx / PHP / MySQL 見「<a href="{{ $urls['env'] ?? '#' }}">環境</a>」。</p>
    </details>
</div>
