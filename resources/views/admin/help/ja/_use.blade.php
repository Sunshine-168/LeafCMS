@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<p class="muted">LeafCMS は Laravel で書いたサイト構築ツールです。ライセンスは MIT です。<strong>テーマ</strong>が見た目を決め、<strong>コンテンツモデル</strong>が項目を決めます。アプリストアもオンライン更新もありません。この案内は README と同じ内容です。</p>
<p class="muted">サイドバーの各項目は <a href="{{ $urls['helpAdmin'] ?? '#' }}">管理</a>、見た目の切り替えは <a href="{{ $urls['helpThemes'] ?? '#' }}">テーマ</a>、Blade ファイルは <a href="{{ $urls['theme'] ?? '#' }}">テンプレート</a>、データタグは <a href="{{ $urls['tags'] ?? '#' }}">タグ</a> を見てください。空のサイトではテンプレート編集から始めないでください。まず 1 ページを公開し、記事を 1 本出します。</p>

<div class="help-jumps">
    @if(!empty($urls['settings']))
        <a href="{{ $urls['settings'] }}">サイト名</a>
    @endif
    @if(!empty($urls['themes']))
        <a href="{{ $urls['themes'] }}">テーマを変える</a>
    @endif
    @if(!empty($urls['categories']))
        <a href="{{ $urls['categories'] }}">カテゴリを追加</a>
    @endif
    @if(!empty($urls['entries']))
        <a href="{{ $urls['entries'] }}">記事を書く</a>
    @endif
    @if(!empty($urls['cache']))
        <a href="{{ $urls['cache'] }}">キャッシュを消す</a>
    @endif
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">サイトを見る</a>
</div>

<h3>最初にすること</h3>
<ol class="help-steps">
    <li>{!! $hl('settings', '設定') !!} でサイト名と短い紹介を入れます。</li>
    <li>{!! $hl('themes', 'テーマ') !!} で外観を選び、「このサイトで使う」を押します。足りないモデルは追加され、既存の記事は残ります。</li>
    <li>{!! $hl('categories', 'カテゴリ') !!} でチャンネル（ニュース、製品、私たちについて）を作ります。一覧が項目を知るため、カテゴリにはモデルが必要です。</li>
    <li>{!! $hl('entries', '記事') !!} で文章を書きます。公開権限があれば公開できます。編集者は初期状態では下書きか審査待ちです。</li>
    <li>サイトを開きます。テーマを変えても古いままなら {!! $hl('cache', 'キャッシュ') !!} を消します。</li>
</ol>
<p class="hint">デモデータがあればカテゴリと記事は既にあります。文面を直すだけです。空のサイトはカテゴリから始めます。サイドバーに項目が無いのは、そのアカウントに権限が無いという意味です。</p>

<h3>サイドバーの役割</h3>
<table class="help-doc">
    <thead>
        <tr><th>区分</th><th>すること</th></tr>
    </thead>
    <tbody>
        <tr><td>コンテンツ</td><td>{!! $hl('categories', 'カテゴリ') !!}、{!! $hl('entries', '記事') !!}、{!! $hl('entryTags', 'タグ') !!}、{!! $hl('media', 'メディア') !!}、{!! $hl('submissions', '受信箱') !!}、{!! $hl('comments', 'コメント') !!}。コンテンツ設定には {!! $hl('flags', 'マーク') !!}、{!! $hl('recommends', 'おすすめ') !!}、{!! $hl('models', 'モデル') !!}、{!! $hl('menus', 'メニュー') !!}、{!! $hl('forms', 'フォーム') !!}、{!! $hl('recycle', 'ごみ箱') !!} があります。</td></tr>
        <tr><td>運用</td><td>{!! $hl('members', '会員') !!}、{!! $hl('collectors', '収集') !!}、{!! $hl('cache', 'キャッシュ') !!}（ページキャッシュとディスク HTML）。運用ツールには {!! $hl('friendLinks', '相互リンク') !!}、{!! $hl('sitemap', 'サイトマップ') !!}、{!! $hl('redirects', 'リダイレクト') !!}、{!! $hl('groups', 'グループ') !!} があります。</td></tr>
        <tr><td>システム</td><td>{!! $hl('settings', '設定') !!}、{!! $hl('themes', 'テーマ') !!}、言語、ログ、管理者、権限、このヘルプ。運用権限が無いアカウントでも開けるよう、ヘルプはここにあります。</td></tr>
    </tbody>
</table>

<h3>カテゴリと記事</h3>
<p class="muted">よくあるカテゴリの種類は、文書の<strong>一覧</strong>、<strong>単一ページ</strong>（私たちについて）、<strong>外部リンク</strong>です。記事には URL を決める主カテゴリが 1 つあり、ほかのカテゴリにも出せます。</p>
<p class="muted">モデルが項目を決めます。記事はタイトル、カバー、本文が普通で、製品は価格などを足します。{!! $hl('models', 'モデル') !!} はコピー、JSON 書き出し、読み込みができ、既存の文書は消えません。</p>
<p class="muted">予約公開は、時刻の前はサイトが 404 を返します。下書きには、アクセス数に入らずキャッシュされないプレビューリンクを付けられます。</p>

<h3>メニュー、ブロック、フォーム</h3>
<p class="muted">カテゴリを「メニュー」にし、テーマの <code>@@cmsMenu</code> にまだメニューが無いと、そのカテゴリがヘッダーになります。順番を固定するなら {!! $hl('menus', 'メニュー') !!} でヘッダーメニューを作ります。</p>
<p class="muted">トップのバナーや「最新ニュース」は {!! $hl('recommends', 'おすすめ') !!} です。識別子はテンプレートの <code>name</code> と一致させます。例: <code>home_banner</code>。手入力の項目はメディアから画像を選び、プレビューできます。出力は <code>cms_media($item-&gt;image)</code> です。</p>
<p class="muted">トップの相互リンクは 運用 → 運用ツール → {!! $hl('friendLinks', '相互リンク') !!} にあり、ブロック <code>partners</code>（無ければ <code>friend_links</code>）に保存されます。ナビテーマの「リンク」モデル（URL ディレクトリ）とは別です。</p>
<p class="muted">問い合わせフォームは {!! $hl('forms', 'フォーム') !!} で作り、<code>@@cmsForm</code> で出します。送信は受信箱に入ります。コメントは初期状態で承認待ちです。切り替えは {!! $settingsLink('interact', '設定 → コメント規則') !!} にあります。検索エンジン向け URL 一覧は 運用 → 運用ツール → {!! $hl('sitemap', 'サイトマップ') !!} です。</p>

<h3>会員</h3>
<p class="muted">サイトの <code>/member</code> が登録とログインです。モデルが会員投稿を許し、グループにその権限があれば、会員は下書きを審査に出せます。コメントとゲストコメントは設定でオンにします。</p>

<h3>バックアップとキャッシュ</h3>
<p class="muted">バックアップと復元は システム → システム保守 で、スーパー管理者だけです。robots.txt の追加規則は {!! $settingsLink('seo', '設定 → 検索と統計') !!} にあります。バックアップは <code>php artisan cms:backup</code> でもできます。キャッシュと静的 HTML は {!! $hl('cache', 'キャッシュ') !!} です。nginx とオブジェクトストレージは <a href="{{ $urls['env'] ?? '#' }}">環境</a> にあります。</p>

<h3>よくある質問</h3>
<div class="help-faq">
    <details>
        <summary>テーマを変えたのにサイトが古いまま</summary>
        <p>ページキャッシュが残っています。{!! $hl('cache', 'キャッシュ') !!} で「キャッシュを消す」を押すか、<code>php artisan cms:html-clear</code> を実行します。</p>
    </details>
    <details>
        <summary>記事を書いたのに見えない</summary>
        <p><strong>公開</strong>であること（下書きや審査中ではない）、公開時刻を過ぎていること、正しいカテゴリにあることが必要です。編集者は初期状態では公開できません。スーパー管理者か「記事を公開」権限のある人が公開します。</p>
    </details>
    <details>
        <summary>画像やアップロードが 404</summary>
        <p>プロジェクトのルートで <code>php artisan storage:link</code> を実行し、<code>storage</code> と <code>bootstrap/cache</code> を書き込み可能にします。CDN を使うなら 設定 → CDN と API のドメインを確認します。<code>CMS_MEDIA_DISK=s3</code> のときファイルは <code>public/storage</code> にありません。バケットと <code>AWS_URL</code> を確認します。</p>
    </details>
    <details>
        <summary>ブロックやフォームが空</summary>
        <p>管理画面の識別子がテンプレートと一致している必要があります。ブロックの <code>name</code>（例 <code>home_banner</code>）、フォームの <code>slug</code>（例 <code>contact</code>）。無効や空の一覧は出ません。管理画面に画像がありサイトに無いときは、テーマが <code>cms_media($item-&gt;image)</code> を使うようにします。</p>
    </details>
    <details>
        <summary>検索エンジンに一部のパスを見せたくない</summary>
        <p>まず <code>/robots.txt</code> を開きます（管理画面、会員、インストール、プレビューは既に禁止）。{!! $settingsLink('seo', '設定 → 検索と統計') !!} に行を足します。例: <code>Disallow: /private</code>。サイト閉鎖中はファイル全体が <code>Disallow: /</code> のままです。HTML タグは書かないでください。収録範囲は 運用 → 運用ツール → {!! $hl('sitemap', 'サイトマップ') !!} です。</p>
    </details>
    <details>
        <summary>サイトマップはどこ</summary>
        <p>検索エンジンは <code>/sitemap.xml</code>（英語は <code>/en/sitemap.xml</code>）を見ます。訪問者は <code>/sitemap</code> を開けます。そのページを切るとフッターのリンクも消えます。カテゴリ、記事、タグ、追加 URL は 運用 → 運用ツール → {!! $hl('sitemap', 'サイトマップ') !!} です。会員限定の記事と公開記事の無いタグは入りません。カテゴリの識別子に sitemap は使わないでください。</p>
    </details>
    <details>
        <summary>インストーラをもう一度</summary>
        <p><code>storage/app/install.lock</code> を消して <code>/install</code> を開きます。既存データは消えません。公開中のサイトではやらないでください。</p>
    </details>
    <details>
        <summary>公開サイトがディレクトリ一覧か 404</summary>
        <p>ウェブルートはプロジェクトのルートではなく <code>public</code> フォルダです。nginx、PHP、MySQL は <a href="{{ $urls['env'] ?? '#' }}">環境</a> にあります。</p>
    </details>
</div>
