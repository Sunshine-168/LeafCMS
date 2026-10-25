@php
    $urls = $urls ?? [];
@endphp
<p class="muted">前臺我只留了一套標籤：<code>@@cms*</code>。循環要成對寫結束指令。循環變量預設 <code>$item</code>，嵌套時必須用 <code>'as' =&gt; '別名'</code>，否則兩層會搶同一個名字。換哪套外觀見「<a href="{{ $urls['helpThemes'] ?? '#' }}">主題</a>」，檔案放哪見「<a href="{{ $urls['theme'] ?? '#' }}">模板</a>」。</p>

<h3>常用例子</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
@verbatim
<pre><code>@cmsChannel(['type' => 'top', 'ismenu' => 1])
    &lt;a href="{{ $item->frontUrl() }}"&gt;{{ $item->t('title') }}&lt;/a&gt;
@endcmsChannel

@cmsContent(['model' => 'article', 'num' => 6])
    &lt;a href="{{ $item->url }}"&gt;{{ $item->t('title') }}&lt;/a&gt;
@endcmsContent

@cmsAdv(['name' => 'home_banner'])
    &lt;img src="{{ cms_media($item->image) }}" alt="{{ $item->t('title') }}"&gt;
@endcmsAdv</code></pre>
@endverbatim
</div>

<h3>欄目列表頁</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
@verbatim
<pre><code>@cmsContent(['page' => true, 'num' => $category->page_size ?: 10])
    &lt;article&gt;
        &lt;div&gt;@cmsDate(['name' => $item->published_at])&lt;/div&gt;
        &lt;h3&gt;&lt;a href="{{ $item->url }}"&gt;{{ $item->t('title') }}&lt;/a&gt;&lt;/h3&gt;
        &lt;p&gt;@cmsSubstr(['name' => $item->t('excerpt') ?: $item->t('content'), 'len' => 100])&lt;/p&gt;
    &lt;/article&gt;
@endcmsContent
@cmsPaginate</code></pre>
@endverbatim
</div>

<h3>詳情頁</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
@verbatim
<pre><code>@cmsBreadcrumb
&lt;h1&gt;{{ $entry->t('title') }}&lt;/h1&gt;
&lt;div&gt;{!! $entry->linked_content ?? $entry->t('content') !!}&lt;/div&gt;
&lt;p&gt;上一篇：@cmsPrev 下一篇：@cmsNext&lt;/p&gt;</code></pre>
@endverbatim
</div>

<h3>嵌套循環（必須改名）</h3>
<div class="code">
    <button type="button" class="copy" data-copy>複製</button>
@verbatim
<pre><code>@cmsChannel(['type' => 'top', 'as' => 'cat'])
    &lt;h2&gt;{{ $cat->t('title') }}&lt;/h2&gt;
    @cmsContent(['catid' => $cat->id, 'num' => 4, 'as' => 'row'])
        &lt;a href="{{ $row->url }}"&gt;{{ $row->t('title') }}&lt;/a&gt;
    @endcmsContent
@endcmsChannel</code></pre>
@endverbatim
</div>

<h3>指令一覽</h3>
<table class="help-doc">
    <thead>
        <tr><th>指令</th><th>作用</th><th>常用參數</th></tr>
    </thead>
    <tbody>
        <tr><td><code>@@cmsSeo</code></td><td>輸出 title / keywords / description</td><td>不用參數，放在 <code>&lt;head&gt;</code></td></tr>
        <tr><td><code>@@cmsChannel</code></td><td>欄目循環</td><td><code>type=top|son|peer|selftop</code>，<code>ismenu</code>，<code>num</code>，<code>name</code>（欄目 id 或 slug）</td></tr>
        <tr><td><code>@@cmsMenu</code></td><td>後臺導航；沒有菜單時回退欄目</td><td><code>location=header</code>（預設）</td></tr>
        <tr><td><code>@@cmsContent</code></td><td>文檔列表</td><td><code>catid</code>，<code>model</code>，<code>flag</code>，<code>num</code>，<code>page</code>，<code>order</code>，<code>insub</code>，<code>related</code> + <code>related_field</code></td></tr>
        <tr><td><code>@@cmsPaginate</code></td><td>分頁（配合 <code>page=&gt;true</code>）</td><td>可傳入分頁視圖名</td></tr>
        <tr><td><code>@@cmsArcone</code></td><td>指定一篇</td><td><code>aid</code> 或 <code>id</code></td></tr>
        <tr><td><code>@@cmsAdv</code></td><td>推薦位</td><td><code>name</code> 必填；<code>num</code>；圖片用 <code>cms_media($item-&gt;image)</code>；HTML 條目輸出 <code>$item-&gt;html</code>。首頁友鏈用 <code>partners</code> 或 <code>friend_links</code></td></tr>
        <tr><td><code>@@cmsBreadcrumb</code></td><td>面包屑</td><td><code>symbol</code>，<code>class</code></td></tr>
        <tr><td><code>@@cmsPrev</code> / <code>@@cmsNext</code></td><td>同欄目上下篇</td><td><code>field</code>，<code>msg</code>（沒有時的文字）</td></tr>
        <tr><td><code>@@cmsDate</code></td><td>格式化日期</td><td><code>name</code> 或 <code>value</code>，<code>format=Y-m-d</code></td></tr>
        <tr><td><code>@@cmsSubstr</code></td><td>截斷純文本</td><td><code>name</code>，<code>len=80</code>，<code>dot</code></td></tr>
        <tr><td><code>@@cmsFilter</code></td><td>模型篩選字段</td><td><code>model</code>；配合地址欄 <code>?filter[字段]=值</code></td></tr>
        <tr><td><code>@@cmsOrder</code></td><td>排序鏈接</td><td>輸出 <code>$item-&gt;url</code> / <code>label</code> / <code>current</code></td></tr>
        <tr><td><code>@@cmsFileinfo</code></td><td>附件資訊</td><td><code>field</code> 或 <code>name</code>，<code>aid</code></td></tr>
        <tr><td><code>@@cmsForm</code></td><td>整份表單（含驗證碼）</td><td><code>slug</code>，<code>captcha</code>，<code>submit</code></td></tr>
        <tr><td><code>@@cmsFormFields</code></td><td>只循環字段，自己畫表單</td><td><code>slug</code></td></tr>
        <tr><td><code>@@cmsTaglist</code></td><td>標籤雲</td><td><code>num=20</code></td></tr>
        <tr><td><code>@@cmsMap</code></td><td>嵌入 OpenStreetMap</td><td><code>point=經度,緯度</code>，<code>address</code>，<code>width</code>，<code>height</code></td></tr>
    </tbody>
</table>

<h3>@@cmsContent 再說明</h3>
<p class="muted">不寫 <code>catid</code> 時，列表頁預設當前欄目（含子欄目，<code>insub=&gt;false</code> 可關掉）。<code>catid=&gt;0</code> 或 <code>'all'</code> 不限欄目。<code>model</code> 可以是標識（如 <code>article</code>）或模型 id。<code>flag</code> 對推薦屬性。<code>related</code> 按擴展字段找關聯文檔，例如漫畫章節：<code>related=&gt;$entry-&gt;id, related_field=&gt;'manga_id'</code>。</p>

<h3>輔助函數</h3>
<table class="help-doc">
    <thead>
        <tr><th>函數</th><th>作用</th></tr>
    </thead>
    <tbody>
        <tr><td><code>cms_media($path)</code></td><td>附件地址。走「設置」裡的 CDN；<code>.env</code> 配了 <code>CMS_MEDIA_DISK=s3</code> 時指向對象存儲</td></tr>
        <tr><td><code>cms_route('download', $entry)</code></td><td>帶語言前綴的命名路由</td></tr>
        <tr><td><code>cms_front_url('news')</code></td><td>前臺路徑</td></tr>
        <tr><td><code>cms_ui('search')</code></td><td>主題短句。全站預設在「語言 → 主題文案」；某一套可在主題「配置」裡覆蓋。可寫 <code>cms_ui('hello', ['name' => $name])</code> 替換 <code>:name</code>。</td></tr>
        <tr><td><code>cms_theme('home_notice')</code></td><td>這一套主題的自定義參數，在主題「配置」裡填。鍵名來自 <code>theme.json</code> 的 <code>config</code>，也有首頁公告、按鈕等預設項。</td></tr>
        <tr><td><code>cms_t('中文', 'English')</code></td><td>按當前內容語言選一句。只有中英時夠用，新語言請改用 <code>cms_ui()</code>。</td></tr>
    </tbody>
</table>
<p class="hint">正文 HTML 不要用兩個花括號（會轉義標籤），要用 Blade 的 raw 輸出：<code>@verbatim{!! $entry->t('content') !!}@endverbatim</code>。不要在主題裡新寫一套標籤，也不要引用別的 CMS 指令。</p>
