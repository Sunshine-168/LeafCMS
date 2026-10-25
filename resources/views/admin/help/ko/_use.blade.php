@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<p class="muted">LeafCMS는 Laravel로 만든 사이트 빌더입니다. 라이선스는 MIT입니다. <strong>테마</strong>가 모양을 정하고, <strong>콘텐츠 모델</strong>이 필드를 정합니다. 앱 스토어도 온라인 업그레이드도 없습니다. 이 안내는 README와 같은 내용입니다.</p>
<p class="muted">사이드바 항목은 <a href="{{ $urls['helpAdmin'] ?? '#' }}">관리</a>, 스킨 변경은 <a href="{{ $urls['helpThemes'] ?? '#' }}">테마</a>, Blade 파일은 <a href="{{ $urls['theme'] ?? '#' }}">템플릿</a>, 데이터 태그는 <a href="{{ $urls['tags'] ?? '#' }}">태그</a>를 보세요. 빈 사이트에서는 템플릿 수정부터 시작하지 마세요. 페이지 하나를 올리고 글 하나를 먼저 발행하세요.</p>

<div class="help-jumps">
    @if(!empty($urls['settings']))
        <a href="{{ $urls['settings'] }}">사이트 이름</a>
    @endif
    @if(!empty($urls['themes']))
        <a href="{{ $urls['themes'] }}">테마 변경</a>
    @endif
    @if(!empty($urls['categories']))
        <a href="{{ $urls['categories'] }}">섹션 추가</a>
    @endif
    @if(!empty($urls['entries']))
        <a href="{{ $urls['entries'] }}">글 쓰기</a>
    @endif
    @if(!empty($urls['cache']))
        <a href="{{ $urls['cache'] }}">캐시 지우기</a>
    @endif
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">사이트 보기</a>
</div>

<h3>먼저 할 일</h3>
<ol class="help-steps">
    <li>{!! $hl('settings', '설정') !!}에서 사이트 이름과 짧은 소개를 넣습니다.</li>
    <li>{!! $hl('themes', '테마') !!}에서 스킨을 고르고 “이 사이트에서 사용”을 누릅니다. 없는 모델은 추가되고, 있는 글은 남습니다.</li>
    <li>{!! $hl('categories', '섹션') !!}에서 채널(뉴스, 제품, 소개)을 만듭니다. 목록이 필드를 알려면 섹션마다 모델이 필요합니다.</li>
    <li>{!! $hl('entries', '글') !!}에서 기사를 씁니다. 발행 권한이 있으면 바로 공개할 수 있고, 편집자는 기본적으로 초안이나 검토 대기입니다.</li>
    <li>사이트를 엽니다. 테마를 바꾼 뒤에도 옛 모습이면 {!! $hl('cache', '캐시') !!}를 지웁니다.</li>
</ol>
<p class="hint">데모 데이터가 있으면 섹션과 글이 이미 있습니다. 문장만 고치면 됩니다. 빈 사이트는 섹션부터 시작합니다. 사이드바에 항목이 없으면 이 계정에 권한이 없다는 뜻입니다.</p>

<h3>사이드바의 역할</h3>
<table class="help-doc">
    <thead>
        <tr><th>영역</th><th>하는 일</th></tr>
    </thead>
    <tbody>
        <tr><td>콘텐츠</td><td>{!! $hl('categories', '섹션') !!}, {!! $hl('entries', '글') !!}, {!! $hl('entryTags', '태그') !!}, {!! $hl('media', '미디어') !!}, {!! $hl('submissions', '받은편지함') !!}, {!! $hl('comments', '댓글') !!}. 콘텐츠 설정에는 {!! $hl('flags', '표시') !!}, {!! $hl('recommends', '추천') !!}, {!! $hl('models', '모델') !!}, {!! $hl('menus', '메뉴') !!}, {!! $hl('forms', '양식') !!}, {!! $hl('recycle', '휴지통') !!}이 있습니다.</td></tr>
        <tr><td>운영</td><td>{!! $hl('members', '회원') !!}, {!! $hl('collectors', '수집') !!}, {!! $hl('cache', '캐시') !!}(페이지 캐시와 디스크 HTML). 운영 도구에는 {!! $hl('friendLinks', '링크') !!}, {!! $hl('sitemap', '사이트맵') !!}, {!! $hl('redirects', '리다이렉트') !!}, {!! $hl('groups', '그룹') !!}이 있습니다.</td></tr>
        <tr><td>시스템</td><td>{!! $hl('settings', '설정') !!}, {!! $hl('themes', '테마') !!}, 언어, 로그, 관리자, 권한, 이 도움말. 운영 권한이 없는 계정도 열 수 있도록 도움말은 여기에 있습니다.</td></tr>
    </tbody>
</table>

<h3>섹션과 글</h3>
<p class="muted">흔한 섹션 종류는 문서 <strong>목록</strong>, <strong>단일 페이지</strong>(소개), <strong>외부 링크</strong>입니다. 글에는 URL을 정하는 주 섹션이 하나 있고, 다른 섹션에도 넣을 수 있습니다.</p>
<p class="muted">모델이 필드를 정합니다. 글은 보통 제목, 표지, 본문이고 제품은 가격 등을 더합니다. {!! $hl('models', '모델') !!}은 복사, JSON 내보내기, 가져오기가 되며 기존 문서는 지우지 않습니다.</p>
<p class="muted">예약 발행은 시각 전에 사이트가 404를 돌려줍니다. 초안에는 조회수에 안 잡히고 캐시되지 않는 미리보기 링크를 줄 수 있습니다.</p>

<h3>메뉴, 블록, 양식</h3>
<p class="muted">섹션을 “메뉴”로 표시하고 테마의 <code>@@cmsMenu</code>에 아직 메뉴가 없으면 그 섹션이 헤더가 됩니다. 순서를 고정하려면 {!! $hl('menus', '메뉴') !!}에서 헤더 메뉴를 만듭니다.</p>
<p class="muted">첫 화면 배너와 “최신 소식” 블록은 {!! $hl('recommends', '추천') !!}입니다. 식별자는 템플릿 <code>name</code>과 같아야 합니다. 예: <code>home_banner</code>. 직접 넣은 항목은 미디어에서 그림을 고르고 미리 볼 수 있습니다. 출력은 <code>cms_media($item-&gt;image)</code>입니다.</p>
<p class="muted">첫 화면 링크는 운영 → 운영 도구 → {!! $hl('friendLinks', '링크') !!}에 있고, 블록 <code>partners</code>(없으면 <code>friend_links</code>)에 저장됩니다. 내비게이션 테마의 “링크” 모델(URL 목록)과는 다릅니다.</p>
<p class="muted">문의 양식은 {!! $hl('forms', '양식') !!}에서 만들고 <code>@@cmsForm</code>로 출력합니다. 제출은 받은편지함으로 갑니다. 댓글은 기본적으로 승인을 기다립니다. 스위치는 {!! $settingsLink('interact', '설정 → 댓글 규칙') !!}에 있습니다. 검색엔진용 URL 목록은 운영 → 운영 도구 → {!! $hl('sitemap', '사이트맵') !!}입니다.</p>

<h3>회원</h3>
<p class="muted">사이트의 <code>/member</code>가 가입과 로그인입니다. 모델이 회원 글을 허용하고 그룹에 권한이 있으면 회원은 초안을 검토에 낼 수 있습니다. 댓글과 손님 댓글은 설정에서 켭니다.</p>

<h3>백업과 캐시</h3>
<p class="muted">백업과 복원은 시스템 → 시스템 유지보수에 있고 최고 관리자만 할 수 있습니다. robots.txt 추가 규칙은 {!! $settingsLink('seo', '설정 → 검색과 통계') !!}에 있습니다. 백업은 <code>php artisan cms:backup</code>으로도 됩니다. 캐시와 정적 HTML은 {!! $hl('cache', '캐시') !!}입니다. nginx와 객체 저장소는 <a href="{{ $urls['env'] ?? '#' }}">환경</a>에 있습니다.</p>

<h3>자주 묻는 질문</h3>
<div class="help-faq">
    <details>
        <summary>테마를 바꿨는데 사이트가 옛 모습입니다</summary>
        <p>페이지 캐시가 남아 있습니다. {!! $hl('cache', '캐시') !!}에서 “캐시 지우기”를 누르거나 <code>php artisan cms:html-clear</code>를 실행하세요.</p>
    </details>
    <details>
        <summary>글을 썼는데 보이지 않습니다</summary>
        <p><strong>발행</strong> 상태여야 하고(초안이나 검토 중이 아님), 발행 시각이 지났으며, 맞는 섹션에 있어야 합니다. 편집자는 기본적으로 발행할 수 없습니다. 최고 관리자나 “글 발행” 권한이 있는 사람이 발행합니다.</p>
    </details>
    <details>
        <summary>이미지나 업로드가 404입니다</summary>
        <p>프로젝트 루트에서 <code>php artisan storage:link</code>를 실행하고 <code>storage</code>와 <code>bootstrap/cache</code>를 쓸 수 있게 두세요. CDN을 쓰면 설정 → CDN과 API의 도메인을 확인하세요. <code>CMS_MEDIA_DISK=s3</code>이면 파일은 <code>public/storage</code>에 없습니다. 버킷과 <code>AWS_URL</code>을 확인하세요.</p>
    </details>
    <details>
        <summary>블록이나 양식이 비어 있습니다</summary>
        <p>관리자의 식별자가 템플릿과 같아야 합니다. 블록 <code>name</code>(예: <code>home_banner</code>), 양식 <code>slug</code>(예: <code>contact</code>). 끄거나 빈 목록은 나오지 않습니다. 관리자에는 그림이 있는데 사이트에 없으면 테마가 <code>cms_media($item-&gt;image)</code>를 써야 합니다.</p>
    </details>
    <details>
        <summary>검색엔진이 일부 경로를 건너뛰게 하고 싶습니다</summary>
        <p>먼저 <code>/robots.txt</code>를 엽니다(관리자, 회원, 설치, 미리보기는 이미 금지). {!! $settingsLink('seo', '설정 → 검색과 통계') !!}에 줄을 넣습니다. 예: <code>Disallow: /private</code>. 사이트를 닫으면 파일 전체가 <code>Disallow: /</code>로 남습니다. HTML 태그는 쓰지 마세요. 수집 범위는 운영 → 운영 도구 → {!! $hl('sitemap', '사이트맵') !!}입니다.</p>
    </details>
    <details>
        <summary>사이트맵은 어디에 있나요</summary>
        <p>검색엔진은 <code>/sitemap.xml</code>(영어는 <code>/en/sitemap.xml</code>)을 봅니다. 방문자는 <code>/sitemap</code>을 엽니다. 그 페이지를 끄면 바닥글 링크도 사라집니다. 섹션, 글, 태그, 추가 URL은 운영 → 운영 도구 → {!! $hl('sitemap', '사이트맵') !!}입니다. 회원 전용 글과 공개 글이 없는 태그는 빠집니다. 섹션 식별자로 sitemap을 쓰지 마세요.</p>
    </details>
    <details>
        <summary>설치 화면을 다시 열고 싶습니다</summary>
        <p><code>storage/app/install.lock</code>을 지우고 <code>/install</code>을 엽니다. 기존 데이터는 지워지지 않습니다. 운영 중인 사이트에서는 하지 마세요.</p>
    </details>
    <details>
        <summary>공개 사이트가 폴더 목록이나 404를 보입니다</summary>
        <p>웹 루트는 프로젝트 루트가 아니라 <code>public</code> 폴더여야 합니다. nginx, PHP, MySQL은 <a href="{{ $urls['env'] ?? '#' }}">환경</a>에 있습니다.</p>
    </details>
</div>
