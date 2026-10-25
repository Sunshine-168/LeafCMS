<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>安装完毕 - LeafCMS</title>
    <link rel="icon" href="{{ asset('assets/brand/logo.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/css/install.css') }}?v=1">
</head>
<body>
<div class="wrap">
    <div class="brand"><img class="logo" src="{{ asset('assets/brand/logo.svg') }}" alt="" width="32" height="32"> LeafCMS 安装向导</div>
    <div class="card" style="display:block">
        <div class="done">
            <div class="mark">✓</div>
            <h1>安装完毕</h1>
            <p class="muted"><strong>{{ $siteName }}</strong> 可以使用了。请用 <strong>{{ $email }}</strong> 和刚才设置的密码登录后台。</p>
            @if($demo)
                <p class="hint">示例栏目和文章已经导入。上线前请改掉演示账号，不要把默认密码留在公网。</p>
            @endif
            @php
                $siteRoot = str_replace('\\', '/', rtrim(base_path(), '/\\'));
                $artisan = \App\Support\PhpCli::quote($siteRoot.'/artisan');
                $baotaCron = \App\Support\PhpCli::baotaScheduleCommand();
                if (! str_contains($baotaCron, $siteRoot.'/artisan')) {
                    $baotaCron = '/www/server/php/84/bin/php '.$artisan.' schedule:run >> /dev/null 2>&1';
                }
            @endphp
            <div class="install-next">
                <h2>还差这一步</h2>
                <p>宝塔用户：计划任务新建 Shell 脚本，周期选每分钟，粘贴下面这一行。不配的话，定时采集和缓存清理不会自己跑。</p>
                <div class="code">
                    <button type="button" class="copy" data-copy>复制</button>
                    <pre><code>{{ $baotaCron }}</code></pre>
                </div>
                <p class="hint">命令里要是 <code>bin/php</code>，不要用 <code>php-fpm</code>。脚本框不要再加 <code>* * * * *</code>。照着哪一栏填，看 <a href="{{ url('/install?way=baota') }}#baota-guide">宝塔图解</a>。</p>
            </div>
            <div class="btns">
                <a class="btn" href="{{ url('/admin/login') }}">进入后台</a>
                <a class="btn-muted" href="{{ url('/') }}">打开网站</a>
                <a class="btn-muted" href="{{ url('/install?way=baota') }}">宝塔图解</a>
                <a class="btn-muted" href="{{ url('/install?way=deploy') }}">上线部署说明</a>
            </div>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var code = btn.parentElement && btn.parentElement.querySelector('code');
        if (!code) return;
        var text = code.innerText;
        function done() {
            btn.textContent = '已复制';
            setTimeout(function () { btn.textContent = '复制'; }, 1500);
        }
        function fallback() {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); done(); } catch (e) {}
            document.body.removeChild(ta);
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done).catch(fallback);
        } else {
            fallback();
        }
    });
});
</script>
</body>
</html>
