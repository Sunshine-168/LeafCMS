@php
    $themeCards = [
        ['enterprise', '企业', 'Company', '公司官网。适合发布新闻、公司介绍、产品和招聘信息。', 'A company website for news, about pages, products and jobs.', ['article', 'page', 'product', 'job']],
        ['mobile', '移动', 'Mobile', '手机上的阅读外观。可以按访问设备或域名自动切换。', 'A reading theme for phones, selected by device or domain.', ['mobile']],
        ['blog', '博客', 'Blog', '以文章为中心，用时间顺序展示持续更新的内容。', 'An article-focused publication with a chronological reading layout.', ['article']],
        ['download', '下载站', 'Downloads', '整理安装包、说明书和文档，支持记录下载次数。', 'Organize packages, manuals and documents, with download counts.', ['download']],
        ['nav', '导航', 'Directory', '把工具、网站与常用资源按分类集中展示。', 'Curate tools, websites and useful resources in categorized lists.', ['link']],
        ['source', '源码', 'Projects', '用卡片展示项目、源码和资源介绍。', 'Present projects, source packages and resources in a catalog.', ['source']],
        ['manga', '漫画', 'Manga', '作品与章节分别管理，适合连载内容。', 'Manage works and chapters separately for serialized content.', ['manga', 'chapter']],
        ['sports', '体育', 'Sports', '展示赛事资讯与比赛信息。', 'Present sports news and match information.', ['signal']],
        ['default', '默认回退', 'Default fallback', '缺页时提供基础模板，沿用企业主题的对应页面。', 'A base fallback that uses the corresponding company-theme page when needed.', ['fallback']],
    ];
    $modelLabels = [
        'article' => cms_t('文章', 'Articles'), 'page' => cms_t('独立页面', 'Pages'),
        'product' => cms_t('产品', 'Products'), 'job' => cms_t('招聘', 'Jobs'),
        'mobile' => cms_t('手机外观', 'Mobile layout'), 'download' => cms_t('下载', 'Downloads'),
        'link' => cms_t('分类链接', 'Links'), 'source' => cms_t('项目展示', 'Projects'),
        'manga' => cms_t('作品', 'Series'), 'chapter' => cms_t('章节', 'Chapters'),
        'signal' => cms_t('赛事信息', 'Match information'), 'fallback' => cms_t('缺页回退', 'Page fallback'),
    ];
@endphp
<div class="wrap themes">
    @foreach($themeCards as [$slug, $titleZh, $titleEn, $descriptionZh, $descriptionEn, $models])
        @php
            $themeTitle = cms_t($titleZh, $titleEn);
            $previewFile = 'assets/brand/theme-previews/'.$slug.'.jpg';
            $previewVersion = is_file(public_path($previewFile)) ? filemtime(public_path($previewFile)) : '1';
            $previewUrl = asset($previewFile).'?v='.$previewVersion;
            $previewTitle = $themeTitle.' · '.cms_t('示例首页', 'Sample homepage');
        @endphp
        <article class="theme" id="{{ $slug }}">
            <a class="theme-preview" href="{{ $previewUrl }}" data-image-viewer data-image-title="{{ $previewTitle }}">
                <img src="{{ $previewUrl }}" alt="{{ $previewTitle }}" width="1265" height="712" loading="lazy" decoding="async">
                <span class="image-enlarge">{{ cms_t('查看大图', 'View larger') }} ↗</span>
            </a>
            <div class="theme-body">
                <h3>{{ $themeTitle }}</h3>
                <p>{{ cms_t($descriptionZh, $descriptionEn) }}</p>
                <div class="pills">
                    @foreach($models as $model)
                        <span class="pill">{{ $modelLabels[$model] }}</span>
                    @endforeach
                </div>
                <div class="theme-actions">
                    <a href="{{ $previewUrl }}" data-image-viewer data-image-title="{{ $previewTitle }}">{{ cms_t('预览截图', 'Preview screenshot') }}</a>
                    <a href="{{ cms_route('site.docs', ['topic' => 'themes']) }}">{{ cms_t('主题使用说明', 'Theme setup guide') }}</a>
                </div>
            </div>
        </article>
    @endforeach
</div>
