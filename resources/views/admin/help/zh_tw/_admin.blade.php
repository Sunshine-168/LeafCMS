@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<p class="muted">這一頁我按側欄把後臺每一項說清楚。想先把站跑起來，回到「<a href="{{ $urls['helpUse'] ?? route('admin.help') }}">怎麼用</a>」。換外觀看「<a href="{{ $urls['helpThemes'] ?? '#' }}">主題</a>」。各功能頁頂部也有一句我寫的說明。</p>

<h3>概覽</h3>
<table class="help-doc">
    <thead>
        <tr><th>頁面</th><th>做什麼</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('dashboard', '儀表盤') !!}</td><td>待審內容、未讀留言、待審評論。快捷入口可在儀表盤裡改。</td></tr>
        <tr><td>{!! $hl('stats', '統計') !!}</td><td>前臺訪問的 PV / UV / IP、熱門頁、來路。另有蜘蛛抓取。後臺路徑不記。</td></tr>
    </tbody>
</table>

<h3>內容</h3>
<table class="help-doc">
    <thead>
        <tr><th>頁面</th><th>做什麼</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('entries', '內容') !!}</td><td>寫文章。有「發布內容」權限才能發布；編輯預設存草稿或待審。可定時發布、生成預覽鏈接、還原上一稿。一篇有一個主欄目（決定網址），可勾附加欄目。</td></tr>
        <tr><td>{!! $hl('categories', '欄目') !!}</td><td>頻道結構。列表出文檔、單頁只顯示一篇、外鏈跳到別的網址。欄目要綁模型。可設列表/詳情模板。</td></tr>
        <tr><td>{!! $hl('entryTags', '標籤') !!}</td><td>給文章打標籤，前臺 <code>/tag/標識</code> 聚合。和「說明 → 標籤」（模板指令）不是一回事。</td></tr>
        <tr><td>{!! $hl('media', '媒體') !!}</td><td>上傳圖片和檔案。正文插圖、推薦位封面也從這裡選。沒有 <code>public/storage</code> 軟鏈時站點會自己回源，不必強求 <code>php artisan storage:link</code>。只換訪問域名在{!! $settingsLink('api', '設定 → 加速與介面') !!}填 CDN；檔案改存 OSS / S3 用 <code>CMS_MEDIA_DISK</code>，見「<a href="{{ $urls['env'] ?? '#' }}">環境</a>」。水印只對本地盤有效。</td></tr>
        <tr><td>{!! $hl('submissions', '留言') !!}</td><td>前臺表單提交的收件箱。可已讀、備注、郵件回複。</td></tr>
        <tr><td>{!! $hl('comments', '評論') !!}</td><td>預設待審核，通過後出現在文章下。開關和營運通知信箱在{!! $settingsLink('interact', '設定 → 評論規則') !!}。</td></tr>
        <tr><td>{!! $hl('recycle', '回收站') !!}</td><td>刪除的內容先在這裡。可還原；徹底刪除不能恢復。側欄在內容「內容設定」裡，內容頁右上角也能進。</td></tr>
        <tr><td>{!! $hl('flags', '推薦') !!}</td><td>寫內容時勾的屬性（如頭條）。模板 <code>@@cmsContent(['flag' =&gt; '屬性標識'])</code> 按它篩選。</td></tr>
        <tr><td>{!! $hl('recommends', '推薦位') !!}</td><td>首頁 Banner 這類區塊。標識碼要和模板 <code>@@cmsAdv(['name' =&gt; 'home_banner'])</code> 一致。可手填條目或按規則取文章。手填條目點「選擇」從媒體庫挑圖，保存前能預覽。友鏈不必進這裡改，用「運營 → 營運工具 → 友鏈」。</td></tr>
        <tr><td>{!! $hl('models', '模型') !!}</td><td>一類內容的字段（價格、附件等）。可複製、導出/導入 JSON，不刪已有文章。勾「允許會員投稿」後前臺會員才能寫這類稿。</td></tr>
        <tr><td>{!! $hl('menus', '導航') !!}</td><td>頂欄菜單。位置如 <code>header</code> 要和 <code>@@cmsMenu</code> 一致。沒建菜單時會用勾了「菜單」的欄目湊。</td></tr>
        <tr><td>{!! $hl('forms', '表單') !!}</td><td>定義聯繫表單字段。標識碼給 <code>@@cmsForm(['slug' =&gt; 'contact'])</code>。提交進留言。</td></tr>
    </tbody>
</table>

<h3>運營</h3>
<table class="help-doc">
    <thead>
        <tr><th>頁面</th><th>做什麼</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('members', '會員') !!}</td><td>前臺注冊用戶名單。可停用。投稿、收藏在會員中心。</td></tr>
        <tr><td>{!! $hl('collectors', '採集') !!}</td><td>從 RSS、JSON 或 HTML 列表把公開內容拉進欄目。先試抓再啟用。只採集你有權使用的源，不爬內網。自動跑需要配 cron，見「<a href="{{ $urls['schedule'] ?? '#' }}">定時</a>」。</td></tr>
        <tr><td>{!! $hl('cache', '緩存') !!}</td><td>全頁緩存（訪問後把整頁存起來）和磁盤靜態頁（寫成 <code>public/html/</code> 檔案）。生成、刪除檔案多時都會分批進行，頁面上有進度。右上角「清空緩存」會清模板/配置和已存頁面。nginx 寫法見「<a href="{{ $urls['env'] ?? '#' }}">環境</a>」。</td></tr>
        <tr><td>{!! $hl('friendLinks', '友鏈') !!}</td><td>首頁友情鏈接 / 合作夥伴。側欄在運營「營運工具」。不是導航站的網址目錄。數據寫在推薦位 <code>partners</code>（沒有則用 <code>friend_links</code>），主題用 <code>@@cmsAdv(['name' =&gt; 'partners'])</code> 輸出。</td></tr>
        <tr><td>{!! $hl('sitemap', '地圖') !!}</td><td>給搜索引擎的站點地圖。側欄在運營「營運工具」。XML 在 <code>/sitemap.xml</code>，網頁版在 <code>/sitemap</code>。可勾欄目 / 文章 / 標籤，並可加補充地址。會員門檻文章、沒有公開文章的標籤不列入。<code>robots.txt</code> 會列出各語言的 XML。</td></tr>
        <tr><td>{!! $hl('redirects', '跳轉') !!}</td><td>舊地址 301/302 到新地址。按站點隔離，命中會計數。停用後規則還在，只是不跳。</td></tr>
        <tr><td>{!! $hl('groups', '分組') !!}</td><td>會員能力（如投稿）。停用後這組前臺權限關掉，人還在名單裡。</td></tr>
    </tbody>
</table>

<h3>系統</h3>
<table class="help-doc">
    <thead>
        <tr><th>頁面</th><th>做什麼</th></tr>
    </thead>
    <tbody>
        <tr><td>{!! $hl('settings', '設定') !!}</td><td>設定按「網站資訊、網站外觀、評論規則、內容網址、圖片上傳、郵件通知、加速與介面、搜尋與統計」八組儲存。Logo 和手機外觀在「網站外觀」；文章網址格式在「內容網址」；SMTP 在「郵件通知」；API Token、CDN 在「加速與介面」；robots.txt 補充規則在「搜尋與統計」。備份還原在「系統 → 系統維護」，僅超級管理員可用。登入頁可找回後臺密碼。對象存儲盤不在表單裡改，見「<a href="{{ $urls['env'] ?? '#' }}">環境</a>」。</td></tr>
        <tr><td>{!! $hl('themes', '主題') !!}</td><td>點某一套進入配置：這一套的多語言短句、要補的模型、自定義參數（<code>cms_theme('鍵')</code>）。「用於本站」會補缺的模型，不改已有文章。詳見「<a href="{{ $urls['helpThemes'] ?? '#' }}">主題</a>」。</td></tr>
        <tr><td>{!! $hl('locales', '語言') !!}</td><td>三件事分開：<strong>內容語言</strong>（文章標籤、/en 前綴）、<strong>後臺界面</strong>（側欄中/英）、<strong>主題文案</strong>（搜索、登錄、評論、會員等短句，用 <code>cms_ui('search')</code>）。導航和推薦位會按已添加的語言出輸入框。譯文統計只算該語言真正填了標題的篇數；停用不刪已有譯文。改語言或主題文案會清全頁緩存和磁盤靜態頁。</td></tr>
        <tr><td>{!! $hl('logs', '日志') !!}</td><td>管理員在後臺做過什麼：改了哪一篇、批量動作、登錄和 IP。IP 旁會標本機、Docker 內網或公網省市。點 IP 可只看這個地址。生成靜態頁的進度請求不記。不能當文章修訂用。</td></tr>
        <tr><td>{!! $hl('profile', '賬號') !!}</td><td>右上角點自己的名字。改顯示名稱和登錄密碼。</td></tr>
        <tr><td>{!! $hl('adminUsers', '管理員') !!}</td><td>後臺賬號和角色。可限制只能改某些欄目。</td></tr>
        <tr><td>{!! $hl('roles', '權限') !!}</td><td>角色勾模塊權限。超級管理員能做所有事，不能刪。「發布內容」單獨一項，沒有就不能點發布。</td></tr>
        <tr><td>說明</td><td>本頁。人人都能打開，不跟運營權限走，所以放在系統最底下，而不是運營「營運工具」。</td></tr>
    </tbody>
</table>
<p class="hint">側欄沒有的項是當前賬號沒權限，不是系統缺功能。模板指令見「<a href="{{ $urls['tags'] ?? '#' }}">標籤</a>」。</p>
