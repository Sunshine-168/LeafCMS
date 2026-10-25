@extends('themes.official.partials.layout')

@section('title', cms_t('主题 — LeafCMS', 'Themes — LeafCMS'))
@section('description', cms_t('LeafCMS 自带企业、博客、下载、导航、源码、漫画、体育和移动主题。换主题只改变前台样子，已发布的内容会保留。', 'LeafCMS includes company, blog, download, directory, source, manga, sports, and mobile themes. Switching a theme keeps published entries.'))
@section('foot', 'LeafCMS · 换主题不丢已发布的内容')

@section('content')
    <div class="page-hero">
        <div class="wrap">
            <div class="kicker"><i></i> Themes</div>
            <h1>{{ cms_t('从适合的主题，开始你的网站。', 'Find a starting point for your website.') }}</h1>
            <p class="lead">{{ cms_t('企业官网、内容博客、资源目录。选择适合的展示方式，再用自己的内容和品牌把它变成你的网站。', 'Company websites, publications and resource libraries. Choose a presentation, then make it yours with your content and brand.') }}</p>
        </div>
    </div>
    <section>
        <div class="wrap catalog-toolbar">
            <div class="theme-filterbar" hidden aria-label="{{ cms_t('按用途筛选主题', 'Filter themes by purpose') }}">
                <button type="button" data-theme-filter="all" aria-pressed="true">{{ cms_t('全部主题', 'All themes') }}</button>
                <button type="button" data-theme-filter="business" aria-pressed="false">{{ cms_t('企业展示', 'Business') }}</button>
                <button type="button" data-theme-filter="publishing" aria-pressed="false">{{ cms_t('内容出版', 'Publishing') }}</button>
                <button type="button" data-theme-filter="resources" aria-pressed="false">{{ cms_t('资源与目录', 'Resources') }}</button>
                <button type="button" data-theme-filter="utility" aria-pressed="false">{{ cms_t('移动与基础', 'Mobile & base') }}</button>
            </div>
            <p id="theme-results" role="status" data-label="{{ cms_t('找到 :count 套主题', ':count themes found') }}">{{ cms_t('8 套用途主题 + 1 套默认回退模板', '8 purpose-built themes + 1 default fallback') }}</p>
            <p class="note">{{ cms_t('下方是内置主题真实模板的首页截图，使用独立中文演示内容，可点击放大。截图不是在线演示，切换主题前仍需核对栏目、模型和模板。', 'Actual homepage screenshots rendered from bundled themes with isolated Chinese sample content. Click to enlarge. These are screenshots, not live demos; check sections, models and templates before switching.') }}</p>
        </div>
        @include('themes.official.partials.theme-cards')
    </section>
    <section>
        <div class="wrap grid-2">
            <article class="card">
                <h3>{{ cms_t('自己加一套', 'Add your own') }}</h3>
                <p>{{ cms_t('可以在后台复制一套现成主题再改，也可以直接上传压缩包。命令行：', 'Copy a theme in the admin, or upload a zip. From the command line:') }}</p>
                <pre><code>php artisan cms:theme-make brand --from=enterprise --name="{{ cms_t('品牌站', 'Brand site') }}"</code></pre>
            </article>
            <article class="card">
                <h3>theme.json</h3>
                <p>{{ cms_t('用来写主题名称、说明，以及它需要的内容类型和初始栏目。放一张预览图后，后台的主题卡片就会显示出来。', 'Name, description, models, and starter sections. A preview image shows on the admin theme card.') }}</p>
            </article>
        </div>
    </section>
@endsection
