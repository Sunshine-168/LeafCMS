@php
    $siteRoot = str_replace('\\', '/', rtrim(base_path(), '/\\'));
    $artisan = \App\Support\PhpCli::quote($siteRoot.'/artisan');
    $baotaFill = \App\Support\PhpCli::baotaScheduleCommand();
    if (! str_contains($baotaFill, $siteRoot.'/artisan')) {
        $baotaFill = '/www/server/php/84/bin/php '.$artisan.' schedule:run >> /dev/null 2>&1';
    }
    $menus = ['首頁', '網站', 'FTP', '資料庫', '監控', '安全', '檔案', '計劃任務', '軟體商店', '設置'];
    $photo = function (string $file): string {
        $name = $file === '2-root.png' ? '3-run.png' : $file;
        $rel = 'assets/images/guide/'.$name;
        $abs = public_path($rel);
        if (! is_file($abs)) {
            return '';
        }

        return asset($rel).'?v='.filemtime($abs);
    };
@endphp
@once
<style>
.baota-lead { margin: 0 0 8px; color: var(--muted, #4e5969); line-height: 1.65; font-size: 15px; }
.baota-steps { list-style: none; margin: 0; padding: 0; }
.baota-steps > li { display: block; padding: 22px 0 6px; border-top: 1px solid var(--line, #e5e6eb); }
.baota-steps > li:first-child { border-top: 0; padding-top: 8px; }
.baota-copy h3 { margin: 0 0 6px; font-size: 20px; }
.baota-copy p { margin: 0 0 12px; color: var(--text, #1d2129); line-height: 1.7; font-size: 15px; }
.baota-fig { margin: 0 0 8px; }
.baota-note { margin: 8px 0 0; font-size: 14px; color: #86909c; line-height: 1.55; }
.bt-photo { display: block; width: 100%; height: auto; border: 1px solid #d0d5dd; border-radius: 8px; }
.bt-shot { border: 1px solid #c5cad3; border-radius: 8px; overflow: hidden; background: #eef0f3; box-shadow: 0 10px 28px rgba(20, 24, 32, .12); }
.bt-chrome { display: flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px; background: #eceff3; border-bottom: 1px solid #d5d9e0; color: #4e5969; font-size: 12px; }
.bt-chrome i { width: 9px; height: 9px; border-radius: 50%; background: #c5c8ce; display: block; }
.bt-chrome b { margin-left: 6px; color: #1d2129; font-weight: 600; }
.bt-app { display: grid; grid-template-columns: 132px minmax(0, 1fr); min-height: 250px; }
.bt-side { background: #2c3338; color: #d5d8de; padding: 8px 0 12px; font-size: 13px; }
.bt-side div { padding: 8px 14px; }
.bt-side .on { background: #20a53a; color: #fff; font-weight: 700; }
.bt-main { padding: 14px; min-width: 0; }
.bt-search { display: flex; gap: 8px; margin-bottom: 10px; }
.bt-search span, .bt-input, .bt-select { background: #fff; border: 1px solid #d9dde3; border-radius: 3px; padding: 7px 10px; font-size: 13px; color: #1d2129; }
.bt-search span { flex: 1; color: #86909c; }
.bt-table { width: 100%; border-collapse: collapse; background: #fff; font-size: 13px; }
.bt-table th, .bt-table td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #eee; }
.bt-table th { background: #fafafa; color: #4e5969; font-weight: 600; }
.bt-table tr.is-dim { color: #a0a6b0; }
.bt-btn { display: inline-block; padding: 4px 12px; border-radius: 3px; background: #20a53a; color: #fff; font-style: normal; font-size: 13px; font-weight: 700; }
.bt-btn.is-plain { background: #fff; color: #4e5969; border: 1px solid #d9dde3; font-weight: 600; }
.bt-hit { outline: 3px solid #e23d3d; outline-offset: 3px; }
.bt-call { display: inline-block; margin-left: 8px; padding: 2px 8px; border-radius: 99px; background: #e23d3d; color: #fff; font-size: 12px; font-weight: 700; vertical-align: middle; }
.bt-form { background: #fff; border: 1px solid #e6e8eb; border-radius: 4px; padding: 14px 16px; display: flex; flex-direction: column; gap: 12px; }
.bt-line { display: grid; grid-template-columns: 88px minmax(0, 1fr); gap: 10px; align-items: center; font-size: 13px; }
.bt-line > span { color: #4e5969; }
.bt-input, .bt-select { display: block; width: 100%; box-sizing: border-box; word-break: break-all; }
.bt-input.is-bad { background: #fdecea; border-color: #e7a3a3; color: #b71c1c; text-decoration: line-through; }
.bt-input.is-good, .bt-select.is-good { background: #e8f5e9; border-color: #8fce96; color: #1b5e20; font-weight: 700; }
.bt-url { padding: 10px 12px; border-radius: 6px; background: #fff; border: 1px solid #8fce96; color: #1b5e20; font-weight: 700; font-size: 16px; word-break: break-all; }
.bt-script { margin: 0; padding: 10px; border-radius: 4px; background: #1e2228; color: #d6f5d6; font-size: 12px; line-height: 1.5; white-space: pre-wrap; word-break: break-all; font-family: Consolas, "SF Mono", Menlo, monospace; }
.bt-browser { background: #fff; }
.bt-addr { display: flex; align-items: center; gap: 8px; padding: 10px 12px; background: #f4f5f7; border-bottom: 1px solid #e5e6eb; }
@media (max-width: 760px) {
    .bt-app { grid-template-columns: 92px minmax(0, 1fr); }
    .bt-side { font-size: 12px; }
    .bt-side div { padding: 7px 8px; }
    .bt-line { grid-template-columns: 72px minmax(0, 1fr); }
    .baota-copy h3 { font-size: 18px; }
}
</style>
@endonce
<div class="card guide" id="baota-guide">
    <div class="pane">
        <h2>寶塔怎麼配</h2>
        <p class="baota-lead">不用學命令。左邊菜單的名字和面板裡一樣，<strong>紅圈就是要點的地方</strong>。面板換了皮膚也沒關係，認字就行。</p>
        <ol class="baota-steps">
            <li>
                <div class="baota-copy">
                    <h3>第 1 步　安裝 PHP 8.4</h3>
                    <p>左邊點「軟體商店」，搜索 PHP。認準 <strong>PHP-8.4</strong> 那一行。右邊還是「安裝」就點安裝；已經是「設置」和「卸載」，就是裝好了。裝完後，網站的 PHP 版本選 8.4。</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('1-php.png') !== '')
                        <img class="bt-photo" src="{{ $photo('1-php.png') }}" alt="軟體商店安裝 PHP 8.4">
                    @else
                    <div class="bt-shot">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>軟體商店</b></div>
                        <div class="bt-app">
                            <aside class="bt-side">
                                @foreach($menus as $name)
                                    <div @class(['on' => $name === '軟體商店'])>{{ $name }}</div>
                                @endforeach
                            </aside>
                            <div class="bt-main">
                                <div class="bt-search"><span>搜索：PHP</span></div>
                                <table class="bt-table">
                                    <thead><tr><th>名稱</th><th>狀態</th><th>操作</th></tr></thead>
                                    <tbody>
                                        <tr class="is-dim"><td>PHP-7.4</td><td>未安裝</td><td><em class="bt-btn is-plain">安裝</em></td></tr>
                                        <tr>
                                            <td>PHP-8.4</td>
                                            <td>未安裝</td>
                                            <td><em class="bt-btn bt-hit">安裝</em><span class="bt-call">點這裡</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">紅圈在 PHP-8.4 右邊的「安裝」。灰色那一行不要當網站 PHP。裝好後點「設置」，打開「禁用函數」，刪掉 putenv、proc_open、shell_exec、exec、symlink、popen。安裝頁會列出還禁用着的名字。</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>第 2 步　運行目錄選 /public</h3>
                    <p>左邊點「網站」，點你的域名，再點「設置」裡的「網站目錄」。「網站目錄」保持項目根，例如 <strong>/www/wwwroot/你的域名</strong>。下面的「運行目錄」從 <strong>/</strong> 改成 <strong>/public</strong>，再點保存。頁面上沒有「運行目錄」時，把網站目錄末尾改成 <strong>/public</strong>。沒改的話，打開首頁是空白的 404，項目根裡的說明頁會告訴你點哪裡。</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('2-root.png') !== '')
                        <img class="bt-photo" src="{{ $photo('2-root.png') }}" alt="網站目錄填寫 public">
                    @else
                    <div class="bt-shot">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>網站 · 設置 · 網站目錄</b></div>
                        <div class="bt-app">
                            <aside class="bt-side">
                                @foreach($menus as $name)
                                    <div @class(['on' => $name === '網站'])>{{ $name }}</div>
                                @endforeach
                            </aside>
                            <div class="bt-main">
                                <div class="bt-form">
                                    <div class="bt-line"><span>域名</span><b class="bt-input">你的域名</b></div>
                                    <div class="bt-line"><span>網站目錄</span><b class="bt-input">/www/wwwroot/你的域名</b></div>
                                    <div class="bt-line"><span>現在</span><b class="bt-input is-bad">運行目錄 /</b></div>
                                    <div class="bt-line">
                                        <span>運行目錄</span>
                                        <b class="bt-input is-good bt-hit">/public</b>
                                    </div>
                                    <div><span class="bt-call">改成 /public，再點保存</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">網站目錄保持項目根。紅字那行是還沒改的運行目錄，綠框 /public 才是要點保存的。</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>第 3 步　偽靜態貼上規則</h3>
                    <p>還是這個設置窗口，左邊點紅框裡的「偽靜態」。白框是空的，就把下面整段貼進去，再點綠色「保存」。上面的「當前」不用改。不貼的話，首頁以外的地址會打不開。</p>
                    <div class="code">
                        <button type="button" class="copy" data-copy>複製</button>
                        <pre><code>location / {
    try_files /html/site_1$uri/index.html /html/site_1$uri $uri $uri/ /index.php?$query_string;
}</code></pre>
                    </div>
                    <p class="hint">靜態頁在 <code>/html/site_1</code>。</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('4-rewrite.png') !== '')
                        <img class="bt-photo" src="{{ $photo('4-rewrite.png') }}" alt="偽靜態白框裡貼上規則並保存">
                    @endif
                    <p class="baota-note">紅框是「偽靜態」、空白規則框和綠色「保存」。規則貼進白框，不要貼進別的地方。</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>第 4 步　兩個資料夾允許寫入</h3>
                    <p>左邊點「檔案」，打開網站目錄，要能看見 <strong>public</strong> 和 <strong>storage</strong>。勾上 <strong>bootstrap</strong> 和 <strong>storage</strong> 兩個資料夾。右上角點「更多」，再點「權限」。所有者保持 <strong>www</strong>，勾上「應用到子目錄」，點綠色「確認」。上面三列勾選不用改。不改的話，安裝頁會打不開。</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('3-perm-open.png') !== '' || $photo('3-perm.png') !== '')
                        @if($photo('3-perm-open.png') !== '')
                            <img class="bt-photo" src="{{ $photo('3-perm-open.png') }}" alt="勾上 bootstrap 和 storage，再點更多裡的權限">
                        @endif
                        @if($photo('3-perm.png') !== '')
                            <img class="bt-photo" src="{{ $photo('3-perm.png') }}" alt="所有者保持 www，勾上應用到子目錄後確認" style="margin-top:12px">
                        @endif
                    @else
                    <div class="bt-shot">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>檔案 · 權限</b></div>
                        <div class="bt-app">
                            <aside class="bt-side">
                                @foreach($menus as $name)
                                    <div @class(['on' => $name === '檔案'])>{{ $name }}</div>
                                @endforeach
                            </aside>
                            <div class="bt-main">
                                <div class="bt-form">
                                    <div class="bt-line"><span>當前目錄</span><b class="bt-input">/www/wwwroot/你的域名</b></div>
                                    <div class="bt-line"><span>選中</span><b class="bt-input">storage、bootstrap/cache</b></div>
                                    <div class="bt-line">
                                        <span>所有者</span>
                                        <b class="bt-input is-good bt-hit">www</b>
                                    </div>
                                    <div><span class="bt-call">所有者填 www，並允許寫入</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">只改這兩個資料夾。不要把整個磁盤都交給 www。</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>第 5 步　用浏覽器打開安裝頁</h3>
                    <p>地址欄輸入你的域名，後面加上 <strong>/install</strong>。頁面上只有三步：環境檢查、資料庫、管理員。自己先試用就選「檔案資料庫」。正式站選 MySQL，先在左邊「資料庫」裡建一個空庫。</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('4-install.png') !== '')
                        <img class="bt-photo" src="{{ $photo('4-install.png') }}" alt="浏覽器打開 /install">
                    @else
                    <div class="bt-shot bt-browser">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>浏覽器</b></div>
                        <div class="bt-addr">
                            <b class="bt-url bt-hit">http://你的域名/install</b>
                            <span class="bt-call">地址欄這樣寫</span>
                        </div>
                        <div class="bt-main">
                            <div class="bt-form">
                                <div class="bt-line"><span>第 1 步</span><b class="bt-input is-good">環境檢查</b></div>
                                <div class="bt-line"><span>第 2 步</span><b class="bt-input">資料庫</b></div>
                                <div class="bt-line"><span>第 3 步</span><b class="bt-input">管理員</b></div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">已經用域名開站了，就不要再運行 php artisan serve。</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>第 6 步　計劃任務，每分鐘一次</h3>
                    <p>左邊最下面點紅框裡的「計劃任務」，再點綠色「添加任務」。任務類型保持 <strong>Shell 腳本</strong>。任務名稱寫「站點定時」。執行週期不要用現在的「每天」，點開改成 <strong>N分鐘</strong>，數字填 <strong>1</strong>。腳本框只粘貼下面這一行，不要自己加星號，也不要寫成 php-fpm。最後點綠色「確定」。不添加這一條，定時採集和緩存清理不會自己跑。</p>
                    <div class="code">
                        <button type="button" class="copy" data-copy>複製</button>
                        <pre><code>{{ $baotaFill }}</code></pre>
                    </div>
                </div>
                <figure class="baota-fig">
                    @if($photo('5-cron-open.png') !== '' || $photo('5-cron.png') !== '')
                        @if($photo('5-cron-open.png') !== '')
                            <img class="bt-photo" src="{{ $photo('5-cron-open.png') }}" alt="左邊點計劃任務，再點添加任務">
                        @endif
                        @if($photo('5-cron.png') !== '')
                            <img class="bt-photo" src="{{ $photo('5-cron.png') }}" alt="計劃任務裡改成每分鐘並貼上命令" style="margin-top:12px">
                        @endif
                    @else
                    <div class="bt-shot">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>計劃任務</b></div>
                        <div class="bt-app">
                            <aside class="bt-side">
                                @foreach($menus as $name)
                                    <div @class(['on' => $name === '計劃任務'])>{{ $name }}</div>
                                @endforeach
                            </aside>
                            <div class="bt-main">
                                <div class="bt-form">
                                    <div class="bt-line"><span>任務類型</span><b class="bt-select is-good bt-hit">Shell 腳本</b></div>
                                    <div class="bt-line"><span>執行週期</span><b class="bt-select is-good">N 分鐘 · 1</b></div>
                                    <div class="bt-line"><span>腳本內容</span><pre class="bt-script">{{ $baotaFill }}</pre></div>
                                    <div><em class="bt-btn bt-hit">添加任務</em><span class="bt-call">填完點這個</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">命令裡要能看到 bin/php。如果出現 php-fpm，換成 bin/php 再添加。</p>
                </figure>
            </li>
        </ol>
    </div>
</div>
