@extends('themes.official.partials.layout')

@section('title', cms_t('安装 — LeafCMS', 'Install — LeafCMS'))
@section('description', cms_t('LeafCMS 可以用 Docker、命令行或网页向导安装。装完即可进入网站前台和后台。', 'Install LeafCMS with Docker, the command line, or the web wizard. Then open the site and the admin.'))
@section('foot', 'LeafCMS · PHP 8.4+ · Laravel 13')

@section('content')
    <div class="page-hero">
        <div class="wrap">
            <div class="kicker"><i></i> Install</div>
            <h1>{{ cms_t('选择你的安装方式。', 'Choose your installation path.') }}</h1>
            <p class="lead">{{ cms_t('下载源码并进入项目目录，下面三种方式选择一种即可，不要依次全部执行。Docker 会准备 PHP 运行环境；命令行与网页向导需要先安装 PHP 8.4+ 和 Composer。以下以全新安装、本机默认端口 8000 为例。', 'Download the source and open the project directory. Choose one of the three methods below; do not run all three in sequence. Docker prepares PHP for you. CLI and web installation need PHP 8.4+ and Composer. These instructions assume a fresh installation on the default local port, 8000.') }}</p>
            <div class="install-choices">
                <a href="#install-docker"><b>Docker ↗</b><span>{{ cms_t('已有 Docker，快速本地试用', 'Have Docker? Start a local trial') }}</span></a>
                <a href="#install-cli"><b>{{ cms_t('命令行', 'Command line') }} ↗</b><span>{{ cms_t('已有 PHP 环境，按步骤部署', 'Have PHP? Deploy step by step') }}</span></a>
                <a href="#install-web"><b>{{ cms_t('网页向导', 'Web installer') }} ↗</b><span>{{ cms_t('在浏览器中填写数据库与账号', 'Configure database and account in the browser') }}</span></a>
            </div>
        </div>
    </div>
    <section>
        <div class="wrap stack">
            <article class="card">
                <h3 id="install-docker">01 · Docker</h3>
                <p>{{ cms_t('需要 Docker 和 Compose。第一次启动会安装依赖、创建 SQLite 数据库并导入演示内容，无需再执行命令行安装或网页向导。等待终端显示服务已启动后再访问。', 'Requires Docker and Compose. The first start installs dependencies, creates the SQLite database, and loads demo content. You do not need the CLI installer or web wizard afterward. Wait until the terminal reports that the server has started.') }}</p>
                <pre><code>docker compose up --build</code></pre>
                <p class="note">{{ cms_t('前台：', 'Site:') }} <code>http://127.0.0.1:8000/</code><br>{{ cms_t('后台：', 'Admin:') }} <code>http://127.0.0.1:8000/admin/login</code></p>
                <p class="note">{{ cms_t('Docker 使用仓库自带的本地演示账号，管理员邮箱为 admin@example.com，初始密码见源码 README 的「本地种子」说明。请勿直接用演示账号对外开放；正式建站可选择下面的命令行或网页向导，自行设置管理员。', 'Docker uses the bundled local demo account, with admin@example.com as the admin email. Find its initial password in the source README under the local seed accounts. Do not expose the demo account publicly. For a new live site, the CLI or web wizard below lets you set your own administrator.') }}</p>
            </article>
            <article class="card">
                <h3 id="install-cli">02 · {{ cms_t('命令行', 'Command line') }}</h3>
                <p>{{ cms_t('需要 PHP 8.4 和 Composer。默认使用 SQLite。如果改用 MySQL，先填好数据库配置再安装。', 'Needs PHP 8.4 and Composer. SQLite is the default. For MySQL, set the database config first.') }}</p>
                <pre><code>composer install
cp .env.example .env
php artisan key:generate
php artisan cms:install --email=you@example.com
php artisan serve</code></pre>
                <p class="note">{{ cms_t('将 you@example.com 换成自己的邮箱；安装命令会提示输入至少 8 位的管理员密码，输入时不会回显。加上 --demo 才会导入示例栏目和文章。后台用你设置的邮箱和密码登录。', 'Replace you@example.com with your own email. The installer prompts for an admin password of at least 8 characters and hides your input. Add --demo only if you want sample sections and entries. Sign in to the admin with the email and password you set.') }}</p>
                <p class="note">{{ cms_t('Windows 下复制配置文件用', 'On Windows copy the env file with') }} <code>copy .env.example .env</code>{{ cms_t('。服务启动后，前台与后台地址如下。', '. After the server starts, open the site and admin below.') }}<br>{{ cms_t('前台：', 'Site:') }} <code>http://127.0.0.1:8000/</code><br>{{ cms_t('后台：', 'Admin:') }} <code>http://127.0.0.1:8000/admin/login</code></p>
            </article>
            <article class="card">
                <h3 id="install-web">03 · {{ cms_t('网页向导', 'Web wizard') }}</h3>
                <p>{{ cms_t('先执行下面的准备命令，再打开 /install。不要执行上一种方式的 cms:install 命令；数据库和管理员账号会在网页向导中设置。', 'Run the preparation commands below, then open /install. Skip the cms:install command from the previous method: you will configure the database and administrator in the web wizard.') }}</p>
                <pre><code>composer install
cp .env.example .env
php artisan key:generate
php artisan serve</code></pre>
                <p class="note">{{ cms_t('Windows 下复制配置文件用', 'On Windows copy the env file with') }} <code>copy .env.example .env</code>{{ cms_t('。启动后在浏览器打开：', '. Once the server starts, open:') }} <code>http://127.0.0.1:8000/install</code></p>
                <p class="note">{{ cms_t('按「环境检查 → 数据库 → 网站和管理员」完成安装。即使不导入演示内容，也会创建你填写的管理员账号。完成后打开以下地址，用自己填写的邮箱和密码登录。', 'Complete Environment check → Database → Site and administrator. Skipping demo content still creates the admin account you enter. After installation, open the addresses below and sign in with your chosen email and password.') }}<br>{{ cms_t('前台：', 'Site:') }} <code>http://127.0.0.1:8000/</code><br>{{ cms_t('后台：', 'Admin:') }} <code>http://127.0.0.1:8000/admin/login</code></p>
            </article>
            <p class="note">{{ cms_t('如果 8000 端口被占用，命令行与网页方式可改用 php artisan serve --port=8001；Docker 可修改 docker-compose.yml 的宿主机端口。访问地址也要换成实际端口。正式域名部署时，将服务器网站目录指向 public，并按部署文档配置 HTTPS。', 'If port 8000 is busy, CLI and web installations can use php artisan serve --port=8001. For Docker, change the host port in docker-compose.yml. Use that port in the addresses above. For a live domain, point the web server at public and configure HTTPS using the deployment guide.') }}</p>
            <details class="card dev-install">
                <summary>{{ cms_t('仅本地开发：重建演示数据', 'Local development only: rebuild demo data') }}</summary>
                <pre><code>php artisan migrate:fresh --seed
php artisan storage:link</code></pre>
                <p class="note">{{ cms_t('这会删除现有数据库表并重建演示数据，请勿在已有内容的网站执行。仅用于可丢弃的本地环境。', 'This drops existing database tables and rebuilds demo data. Do not run on a site with content. Use only in a disposable local environment.') }}</p>
            </details>
        </div>
    </section>
@endsection
