@extends('themes.official.partials.layout')

@section('title', cms_t('LeafCMS 官网 — Laravel 开源建站系统', 'LeafCMS — open-source Laravel site builder'))
@section('description', cms_t('LeafCMS 是可商用的开源建站系统，用来做企业官网和博客。内容在后台维护，前台用一套标签取出来。', 'LeafCMS is an open-source site builder for company sites and blogs. Content is edited in the admin and pulled into themes with tags.'))

@section('content')
    <section class="hero product-hero">
        <div class="wrap hero-grid">
            <div>
                <div class="kicker"><i></i> {{ cms_t('开源、自托管、由你掌控', 'OPEN SOURCE. SELF-HOSTED. YOURS.') }}</div>
                <h1>{{ cms_t('内容由你创造', 'Make it yours.') }}<br><em>{{ cms_t('网站由你定义。', 'Build on your terms.') }}</em></h1>
                <p class="lead">{{ cms_t('从企业官网到个人博客，用一套轻量的 Laravel CMS，把内容和主题放在一起。', 'From a company website to a personal publication. Keep content and themes together in a lightweight Laravel CMS.') }}</p>
                <div class="actions">
                    <a class="btn btn-solid" href="{{ cms_route('site.guide') }}">{{ cms_t('开始构建', 'Start building') }} ↗</a>
                    <a class="btn btn-line" href="{{ cms_route('site.themes') }}">{{ cms_t('探索主题', 'Explore themes') }} →</a>
                </div>
                <p class="fine hero-trust">MIT {{ cms_t('开源许可', 'license') }} <span>PHP 8.4+</span> <span>Laravel 13</span></p>
            </div>
            <div class="product-visual">
                <div class="visual-caption"><span class="status-dot"></span>{{ cms_t('一个工作台，管理你的网站', 'One workspace for your website') }}<span>LeafCMS</span></div>
                <a class="product-screenshot" href="{{ asset('assets/brand/admin-workspace.jpg') }}" data-image-viewer data-image-title="{{ cms_t('LeafCMS 后台工作台', 'LeafCMS admin workspace') }}">
                    <img src="{{ asset('assets/brand/admin-workspace.jpg') }}" width="1265" height="712" fetchpriority="high" alt="{{ cms_t('LeafCMS 真实后台，展示内容管理、待办和统计', 'Actual LeafCMS admin showing content, tasks and analytics') }}">
                    <span class="image-enlarge">{{ cms_t('点击查看大图', 'View larger image') }} ↗</span>
                </a>
                <div class="visual-note">{{ cms_t('真实后台截图 · 本地演示数据', 'Actual admin screenshot · local demo data') }}<a href="{{ cms_route('site.docs') }}">{{ cms_t('了解工作流程', 'Explore the workflow') }} →</a></div>
            </div>
        </div>
    </section>

    <div class="band">
        <div class="wrap facts">
            <div class="fact"><strong>PHP 8.4+</strong><span>{{ cms_t('一套程序即可建站，数据库可用 SQLite 或 MySQL', 'One program. SQLite or MySQL.') }}</span></div>
            <div class="fact"><strong>MIT</strong><span>{{ cms_t('开源可商用，不需要单独购买授权', 'Open source and commercial use. No separate license to buy.') }}</span></div>
            <div class="fact"><strong>{{ cms_t('8 种用途主题', '8 purpose-built themes') }}</strong><span>{{ cms_t('另含官网主题与默认回退模板', 'Plus the official site theme and a default fallback.') }}</span></div>
            <div class="fact"><strong>@@cms*</strong><span>{{ cms_t('前台取栏目、文章和轮播，都用这一套标签', 'Sections, entries, and banners all use this one tag set.') }}</span></div>
        </div>
    </div>

    <section id="features">
        <div class="wrap">
            <div class="sec-head">
                <div>
                    <h2>{{ cms_t('建站时会用到的能力', 'What you use to build a site') }}</h2>
                    <p>{{ cms_t('发文章、换外观、写多种语言，这些都在同一套系统里完成。', 'Publishing, changing the skin, and writing in more than one language all happen in the same system.') }}</p>
                </div>
            </div>
            <div class="grid-3">
                <article class="card"><div class="ico"><svg viewBox="0 0 18 18" fill="none"><path d="M4 4h10M4 9h10M4 14h6" stroke="currentColor" stroke-width="1.6"/></svg></div><h3>{{ cms_t('模板标签', 'Template tags') }}</h3><p>{{ cms_t('导航、文章列表、轮播、分页和留言表单，都在页面里用标签调用，不用把数据查询写进模板。', 'Navigation, lists, banners, pagination, and forms are tags in the page. Themes do not query the database.') }}</p></article>
                <article class="card"><div class="ico"><svg viewBox="0 0 18 18" fill="none"><rect x="3" y="3" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M6 7h6M6 11h4" stroke="currentColor" stroke-width="1.6"/></svg></div><h3>{{ cms_t('内容模型', 'Content models') }}</h3><p>{{ cms_t('文章、产品、下载可以各自带不同字段。换成另一套主题后，已经发布的内容仍然保留。', 'Articles, products, and downloads can have different fields. Published entries stay when you switch themes.') }}</p></article>
                <article class="card"><div class="ico"><svg viewBox="0 0 18 18" fill="none"><path d="M3 14V7l6-4 6 4v7" stroke="currentColor" stroke-width="1.6"/><path d="M7.5 14v-4h3v4" stroke="currentColor" stroke-width="1.6"/></svg></div><h3>{{ cms_t('多语言', 'Languages') }}</h3><p>{{ cms_t('同一篇文章可以写多种语言。前台用 /en 这类地址切换，没填的语言会用默认语言。', 'One entry can be written in several languages. The front uses paths such as /en. A missing translation uses the default language.') }}</p></article>
                <article class="card"><div class="ico"><svg viewBox="0 0 18 18" fill="none"><circle cx="9" cy="6.5" r="2.2" stroke="currentColor" stroke-width="1.6"/><path d="M4.5 14.5c.6-2.2 2.3-3.3 4.5-3.3s3.9 1.1 4.5 3.3" stroke="currentColor" stroke-width="1.6"/></svg></div><h3>{{ cms_t('会员', 'Members') }}</h3><p>{{ cms_t('访客可以注册、登录、收藏和评论。会员提交的稿件需要审核通过后，才会出现在前台。', 'Visitors can register, sign in, save, and comment. Member submissions appear on the front only after review.') }}</p></article>
                <article class="card"><div class="ico"><svg viewBox="0 0 18 18" fill="none"><path d="M9 3v8M6 8.5 9 11.5 12 8.5" stroke="currentColor" stroke-width="1.6"/><path d="M4 14.5h10" stroke="currentColor" stroke-width="1.6"/></svg></div><h3>{{ cms_t('采集', 'Collect') }}</h3><p>{{ cms_t('可以把公开的资讯源按时间写入文章。同一条来源只入库一次，不会整站抓取。', 'Public feeds can be written in on a schedule. The same source item is stored once. It does not crawl a whole site.') }}</p></article>
                <article class="card"><div class="ico"><svg viewBox="0 0 18 18" fill="none"><path d="M4 5.5h10v8H4z" stroke="currentColor" stroke-width="1.6"/><path d="M7 5.5V4h4v1.5" stroke="currentColor" stroke-width="1.6"/></svg></div><h3>{{ cms_t('静态与缓存', 'Static files and cache') }}</h3><p>{{ cms_t('页面可以生成静态文件，也可以整页缓存。文章或栏目改过之后，对应页面会重新生成。', 'Pages can be written to static files or cached. After an entry or section changes, that page is built again.') }}</p></article>
            </div>
        </div>
    </section>


    <section>
        <div class="wrap">
            <div class="sec-head">
                <div>
                    <h2>{{ cms_t('换主题，就是另一种网站', 'Another theme, another kind of site') }}</h2>
                    <p>{{ cms_t('企业站、博客、下载站和导航站共用同一个后台。换主题只改变前台样子。', 'Company, blog, download, and directory themes share one admin. Switching a theme only changes the front.') }}</p>
                </div>
                <a class="btn btn-line" href="{{ cms_route('site.themes') }}">{{ cms_t('全部主题', 'All themes') }}</a>
            </div>
            <div class="cases">
                <a class="case" href="{{ cms_route('site.themes') }}#enterprise">
                    <div class="mini mini-ent" aria-hidden="true">
                        <div class="mini-bar"><b>{{ cms_t('企业官网', 'Company') }}</b><span>{{ cms_t('新闻', 'News') }}</span><span>{{ cms_t('产品', 'Work') }}</span><span>{{ cms_t('招聘', 'Jobs') }}</span></div>
                        <div class="mini-hero"></div>
                        <div class="mini-row"><span><i></i>{{ cms_t('方案', 'Plans') }}</span><span><i></i>{{ cms_t('案例', 'Cases') }}</span><span><i></i>{{ cms_t('新闻', 'News') }}</span></div>
                    </div>
                    <div class="case-body">
                        <h3>{{ cms_t('企业官网', 'Company site') }}</h3>
                        <p>{{ cms_t('新闻、产品、单页和招聘。适合公司介绍和持续更新的栏目。', 'News, products, pages, and jobs.') }}</p>
                    </div>
                </a>
                <a class="case" href="{{ cms_route('site.themes') }}#blog">
                    <div class="mini mini-blog" aria-hidden="true">
                        <div class="mini-bar"><b>{{ cms_t('资讯博客', 'Blog') }}</b><span>{{ cms_t('专栏', 'Column') }}</span><span>{{ cms_t('归档', 'Archive') }}</span></div>
                        <div class="mini-list">
                            <span><b>{{ cms_t('本周更新', 'This week') }}</b><i>{{ cms_t('列表', 'List') }}</i></span>
                            <span><b>{{ cms_t('栏目文章', 'Section') }}</b><i>{{ cms_t('列表', 'List') }}</i></span>
                            <span><b>{{ cms_t('阅读全文', 'Article') }}</b><i>{{ cms_t('详情', 'Detail') }}</i></span>
                        </div>
                    </div>
                    <div class="case-body">
                        <h3>{{ cms_t('资讯博客', 'Blog') }}</h3>
                        <p>{{ cms_t('栏目围绕文章展开，适合持续写内容、做列表和详情页。', 'Sections built around articles, lists, and detail pages.') }}</p>
                    </div>
                </a>
                <a class="case" href="{{ cms_route('site.themes') }}#download">
                    <div class="mini mini-down" aria-hidden="true">
                        <div class="mini-bar"><b>{{ cms_t('资料下载', 'Downloads') }}</b><span>{{ cms_t('文档', 'Docs') }}</span><span>{{ cms_t('安装包', 'Package') }}</span></div>
                        <div class="mini-files">
                            <span><i></i><b>{{ cms_t('说明书', 'Manual') }}</b><em>PDF</em></span>
                            <span><i></i><b>{{ cms_t('安装包', 'Package') }}</b><em>ZIP</em></span>
                        </div>
                    </div>
                    <div class="case-body">
                        <h3>{{ cms_t('资料下载', 'Downloads') }}</h3>
                        <p>{{ cms_t('提供文件下载，并记录每次下载。适合说明书、安装包和资料库。', 'File downloads with a count. Manuals, packages, and libraries.') }}</p>
                    </div>
                </a>
                <a class="case" href="{{ cms_route('site.themes') }}#nav">
                    <div class="mini mini-nav" aria-hidden="true">
                        <div class="mini-bar"><b>{{ cms_t('网址导航', 'Directory') }}</b><span>{{ cms_t('工具', 'Tools') }}</span><span>{{ cms_t('资源', 'Links') }}</span></div>
                        <div class="mini-links"><span>{{ cms_t('设计', 'Design') }}</span><span>{{ cms_t('开发', 'Dev') }}</span><span>{{ cms_t('文档', 'Docs') }}</span><span>{{ cms_t('社区', 'Community') }}</span></div>
                    </div>
                    <div class="case-body">
                        <h3>{{ cms_t('网址导航', 'Directory') }}</h3>
                        <p>{{ cms_t('把常用链接按栏目排开，适合工具站和资源入口。', 'Links grouped by section. Tool lists and resource hubs.') }}</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section>
        <div class="wrap grid-2">
            <div>
                <h2>{{ cms_t('装好就能打开前后台', 'Install, then open both sides') }}</h2>
                <p class="lead">{{ cms_t('Docker、命令行、网页向导，选择一种即可。首次安装完成后，就能进入网站前台和后台。', 'Choose Docker, the command line, or the web wizard. Use one method, then open the site and the admin.') }}</p>
                <div class="steps">
                    <div class="step"><div class="num">1</div><div><strong>{{ cms_t('选择安装方式', 'Choose a method') }}</strong><p class="note">{{ cms_t('已有 Docker 就用右侧命令。已有 PHP 8.4+ 和 Composer，也可按指南选择命令行或网页向导。', 'Use the command here if you have Docker. With PHP 8.4+ and Composer, the guide also offers CLI and web installation.') }}</p></div></div>
                    <div class="step"><div class="num">2</div><div><strong>{{ cms_t('等待安装完成', 'Let installation finish') }}</strong><p class="note">{{ cms_t('Docker 首次启动会准备数据库和演示内容，无需再跑其他安装方式。命令行与网页向导则由你设置管理员。', 'The first Docker start prepares the database and demo content. No other installer is needed. CLI and web installation let you choose your administrator.') }}</p></div></div>
                    <div class="step"><div class="num">3</div><div><strong>{{ cms_t('进入站点', 'Open the site') }}</strong><p class="note">{{ cms_t('默认前台端口为 8000，后台地址以 /admin/login 结尾。登录账号和正式部署注意事项见安装指南。', 'The default local port is 8000. Add /admin/login for the admin. The install guide explains accounts and live deployment.') }}</p></div></div>
                </div>
                <p><a class="btn btn-solid" href="{{ cms_route('site.guide') }}">{{ cms_t('查看完整安装指南', 'Full installation guide') }}</a></p>
            </div>
            <div>
                <h3>{{ cms_t('Docker 本地试用', 'Try locally with Docker') }}</h3>
                <p class="note">{{ cms_t('下载源码，进入项目目录后执行：', 'Download the source, open the project directory, then run:') }}</p>
                <pre><code>docker compose up --build</code></pre>
                <p class="note">{{ cms_t('启动后打开', 'After startup, open') }} <code>http://127.0.0.1:8000/</code><br>{{ cms_t('后台登录', 'Admin sign-in') }} <code>http://127.0.0.1:8000/admin/login</code></p>
                <p class="note">{{ cms_t('网页向导和命令行是另外两种安装方式，具体步骤、管理员账号和端口说明请看完整指南。', 'Web and CLI installation are two alternative methods. See the full guide for their steps, admin accounts, and ports.') }}</p>
            </div>
        </div>
    </section>

    <section>
        <div class="wrap">
            <div class="limit">
                <div>
                    <h2>{{ cms_t('扩展不用换系统', 'Extend it without replacing it') }}</h2>
                    <p>{{ cms_t('新的外观是一套主题目录，可以在后台复制现有主题，也可以上传压缩包。前台始终用同一套标签取内容。', 'A new look is a theme folder. Copy one in the admin or upload a zip. The front keeps using the same tags.') }}</p>
                </div>
                <ul>
                    <li>{{ cms_t('栏目和文章在模板里用标签取出', 'Sections and entries come from tags in the theme') }}</li>
                    <li>{{ cms_t('页面仍然是普通 HTML，方便改版', 'Pages stay ordinary HTML') }}</li>
                    <li>{{ cms_t('交流在', 'Talk in the') }} <a href="https://t.me/applecms" target="_blank" rel="noopener">{{ cms_t('飞机群', 'Telegram group') }}</a></li>
                    <li>{{ cms_t('联系', 'Contact') }} <a href="https://t.me/sanpang" target="_blank" rel="noopener">{{ cms_t('飞机号', 'Telegram') }} @sanpang</a></li>
                </ul>
            </div>
        </div>
    </section>
    <section id="cases">
        <div class="wrap">
            <div class="sec-head">
                <div>
                    <h2>{{ cms_t('也可以看看这些项目', 'Explore these projects, too') }}</h2>
                    <p>{{ cms_t('外部项目展示，不作为 LeafCMS 建站案例或客户背书。', 'External projects, not verified LeafCMS implementations or customer endorsements.') }}</p>
                </div>
            </div>
            <div class="shots">
                <a class="shot" href="https://applecms.top/" target="_blank" rel="noopener">
                    <img src="{{ asset('assets/brand/cases/applecms.png') }}" alt="{{ cms_t('苹果CMS 官网首页', 'AppleCMS homepage') }}" width="1440" height="900" loading="lazy">
                    <div class="shot-meta">
                        <h3>{{ cms_t('苹果CMS', 'AppleCMS') }}</h3>
                        <p>{{ cms_t('访问项目官网，了解其功能与生态。', 'Visit the project website to explore its features and ecosystem.') }}</p>
                        <span>applecms.top</span>
                    </div>
                </a>
                <a class="shot" href="https://xunqitong.im/" target="_blank" rel="noopener">
                    <img src="{{ asset('assets/brand/cases/xunqitong.png') }}" alt="{{ cms_t('讯企通官网首页', 'Xunqitong homepage') }}" width="1440" height="900" loading="lazy">
                    <div class="shot-meta">
                        <h3>{{ cms_t('讯企通', 'Xunqitong') }}</h3>
                        <p>{{ cms_t('企业即时通讯项目，点击查看详情。', 'A business messaging project. Visit its website for details.') }}</p>
                        <span>xunqitong.im</span>
                    </div>
                </a>
            </div>
        </div>
    </section>
@endsection
