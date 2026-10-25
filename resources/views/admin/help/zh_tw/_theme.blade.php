@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
@endphp
<p class="muted">這裡寫主題裡的 Blade 該怎麼改。換哪一套、複製、上傳，見「<a href="{{ $urls['helpThemes'] ?? '#' }}">主題</a>」。自帶主題我希望你先複製一套再改，也可以在主題頁點「編輯模板」。取文章和欄目用 <code>@@cms*</code>，見「<a href="{{ $urls['tags'] ?? '#' }}">標籤</a>」。不要在主題裡寫資料庫查詢。</p>

<h3>目錄長什麼樣</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>resources/views/themes/enterprise/
  theme.json
  preview.png
  index/index.blade.php      首頁（必須有，才能「用於本站」）
  list/list.blade.php        欄目列表
  list/list_product.blade.php 產品列表（可選）
  show/show.blade.php        文章詳情
  show/show_product.blade.php 產品詳情（可選）
  category/category.blade.php 欄目封面/頻道頁
  search.blade.php
  tag.blade.php
  sitemap.blade.php
  sitemap-page.blade.php   訪客可讀的站點地圖（/sitemap）
  partials/layout.blade.php  公共頭尾
  partials/paginate.blade.php
  member/login.blade.php     以及 register、profile 等</code></pre>
</div>
<p class="hint">缺某個檔案時，會去企業主題或 <code>resources/views/cms/</code> 找一份湊上，不會整站空白。</p>

<h3>系統怎麼選模板</h3>
<p class="muted">列表頁：欄目自己的列表模板 → 模型上的 <code>list_tpl</code> → 預設 <code>list.list</code>。詳情頁：文檔擴展字段 <code>show_tpl</code> → 欄目 → 模型 → 預設 <code>show.show</code>。名字按點號對應目錄，例如 <code>list.list_product</code> 就是 <code>list/list_product.blade.php</code>。</p>
<p class="muted">布局優先 <code>partials/layout.blade.php</code>，沒有再用主題根目錄的 <code>layout.blade.php</code>。</p>

<h3>頁面骨架</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
@verbatim
<pre><code>@extends('themes.enterprise.partials.layout')

@section('content')
    @cmsBreadcrumb
    &lt;h1&gt;{{ $category->t('title') }}&lt;/h1&gt;

    @cmsContent(['page' => true])
        &lt;a href="{{ $item->url }}"&gt;{{ $item->t('title') }}&lt;/a&gt;
    @endcmsContent

    @cmsPaginate
@endsection</code></pre>
@endverbatim
</div>
<p class="muted">公共頭裡放 <code>@@cmsSeo</code>。導航用 <code>@@include('cms.partials.header-nav')</code> 或自己寫 <code>@@cmsMenu</code>。</p>

<h3>模板裡能用的變量</h3>
<table class="help-doc">
    <thead>
        <tr><th>變量</th><th>什麼時候有</th><th>常用寫法</th></tr>
    </thead>
    <tbody>
        <tr><td><code>$site</code></td><td>幾乎所有前臺頁</td><td>站名、Logo、主色等設置</td></tr>
        <tr><td><code>$category</code></td><td>欄目、列表</td><td><code>$category-&gt;t('title')</code>、<code>frontUrl()</code>、<code>page_size</code></td></tr>
        <tr><td><code>$entry</code></td><td>詳情</td><td><code>$entry-&gt;t('title')</code>、<code>t('content')</code>、<code>url</code>、<code>cover</code>、<code>views</code></td></tr>
        <tr><td><code>$item</code></td><td>標籤循環裡</td><td>預設循環變量，嵌套時用 <code>'as' =&gt; 'cat'</code> 改名</td></tr>
    </tbody>
</table>
<p class="muted">多語言文案用 <code>t('title')</code>，不要直接 <code>-&gt;title</code>。模型擴展字段用 <code>$item-&gt;attr('price')</code>。圖片地址用 <code>cms_media($item-&gt;cover)</code>，會走站點裡配的 CDN。</p>

<h3>怎麼加一套自己的</h3>
<ol class="help-steps">
    <li>{!! $hl('themes', '主題') !!}頁「複製一套」，或上傳 zip（包內要有 <code>index/index.blade.php</code>），或把資料夾直接放到 <code>themes/</code> 下。</li>
    <li>命令也可以：<code>php artisan cms:theme-make brand --from=enterprise --name=品牌站</code>。</li>
    <li>改 Blade 和 CSS。需要的模型、空欄目寫在 <code>theme.json</code>，點「用於本站」時只補缺的，不改已有字段。</li>
    <li>預覽圖放 <code>preview.png</code>（或 jpg / svg）。</li>
</ol>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>{
  "name": "品牌站",
  "description": "公司官網",
  "i18n": true,
  "responsive": true,
  "models": ["article", "page", "product"],
  "config": {
    "home_notice": {"title": "首頁公告", "type": "textarea", "value": ""}
  },
  "categories": [
    {"slug": "news", "title": {"zh_cn": "新聞中心", "en": "News"}, "model": "article", "type": "list", "menu": true}
  ]
}</code></pre>
</div>
<p class="hint">zip 裡不要帶可執行 PHP。自己主題的模型 JSON 也可放在 <code>models/標識.json</code>，格式和後臺「導出模型」一樣。欄目標題寫成對象後，「用於本站」會按語言寫入；公共頭請留 <code>viewport</code>、語言切換和 <code>cms_ui()</code>。</p>

<h3>二次開發用事件，不要改核心</h3>
<p class="muted">內容保存、評論、表單、換主題、改設置會發出 Laravel 事件。在 <code>app/Providers/AppServiceProvider.php</code> 裡 <code>Event::listen</code> 即可，不必改控制器。倉庫 <code>CONTRIBUTING.md</code> 有同樣一份表。</p>
<table class="help-doc">
    <thead>
        <tr><th>事件</th><th>什麼時候</th></tr>
    </thead>
    <tbody>
        <tr><td><code>App\Events\EntrySaved</code></td><td>保存一篇內容。<code>$created</code> 是否新建；<code>$publishedNow</code> 是否這次變成已發布（會員投稿不會標成剛發布）</td></tr>
        <tr><td><code>App\Events\CommentSubmitted</code></td><td>前臺提交評論（含待審）</td></tr>
        <tr><td><code>App\Events\CommentModerated</code></td><td>後臺通過 / 拒絕 / 標垃圾</td></tr>
        <tr><td><code>App\Events\FormSubmitted</code></td><td>前臺表單提交成功</td></tr>
        <tr><td><code>App\Events\ThemeActivated</code></td><td>當前站換了主題（<code>$slug</code> / <code>$previous</code>）</td></tr>
        <tr><td><code>App\Events\SettingSaved</code></td><td>設置寫入（<code>$keys</code> 是改過的鍵）</td></tr>
    </tbody>
</table>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
<pre><code>use App\Events\EntrySaved;
use Illuminate\Support\Facades\Event;

Event::listen(EntrySaved::class, function (EntrySaved $e) {
    if ($e->publishedNow) {
        // 通知搜索引擎、寫日志等
    }
});</code></pre>
</div>
<p class="hint">列表裡批量改文章狀態不發 <code>EntrySaved</code>，避免一次幾百封郵件。不要為「發布時做什麼」去改核心檔案。</p>
