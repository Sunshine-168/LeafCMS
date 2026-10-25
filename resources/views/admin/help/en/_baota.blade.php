@php
    $siteRoot = str_replace('\\', '/', rtrim(base_path(), '/\\'));
    $artisan = \App\Support\PhpCli::quote($siteRoot.'/artisan');
    $baotaFill = \App\Support\PhpCli::baotaScheduleCommand();
    if (! str_contains($baotaFill, $siteRoot.'/artisan')) {
        $baotaFill = '/www/server/php/84/bin/php '.$artisan.' schedule:run >> /dev/null 2>&1';
    }
    $menus = ['Home', 'Website', 'FTP', 'Database', 'Monitor', 'Security', 'Files', 'Cron', 'App Store', 'Settings'];
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
        <h2>How to set up Baota</h2>
        <p class="baota-lead">You do not need shell commands. The names on the left match the panel, and <strong>the red ring is what you click</strong>. A different skin is fine. Follow the words.</p>
        <ol class="baota-steps">
            <li>
                <div class="baota-copy">
                    <h3>Step 1 — Install PHP 8.4</h3>
                    <p>On the left, open App Store and search for PHP. Use the <strong>PHP-8.4</strong> row. If the button still says Install, click it. Settings and Uninstall mean it is already installed. Then set the site PHP version to 8.4.</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('1-php.png') !== '')
                        <img class="bt-photo" src="{{ $photo('1-php.png') }}" alt="Install PHP 8.4 from App Store">
                    @else
                    <div class="bt-shot">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>App Store</b></div>
                        <div class="bt-app">
                            <aside class="bt-side">
                                @foreach($menus as $name)
                                    <div @class(['on' => $name === 'App Store'])>{{ $name }}</div>
                                @endforeach
                            </aside>
                            <div class="bt-main">
                                <div class="bt-search"><span>Search: PHP</span></div>
                                <table class="bt-table">
                                    <thead><tr><th>Name</th><th>Status</th><th>Action</th></tr></thead>
                                    <tbody>
                                        <tr class="is-dim"><td>PHP-7.4</td><td>Not installed</td><td><em class="bt-btn is-plain">Install</em></td></tr>
                                        <tr>
                                            <td>PHP-8.4</td>
                                            <td>Not installed</td>
                                            <td><em class="bt-btn bt-hit">Install</em><span class="bt-call">Click here</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">The red ring is Install on the PHP-8.4 row. Do not use the grey row as the site PHP. After install, open Settings, then Disabled functions, and remove putenv, proc_open, shell_exec, exec, symlink, and popen. The install page lists any names that are still blocked.</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>Step 2 — Set the run directory to /public</h3>
                    <p>On the left, open Website, click your domain, then Site directory under Settings. Leave Site directory at the project root, for example <strong>/www/wwwroot/your-domain</strong>. Change Run directory from <strong>/</strong> to <strong>/public</strong> and save. If there is no Run directory, change the site directory itself so it ends in <strong>/public</strong>. Otherwise the homepage is a blank 404, and the note in the project root says where to click.</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('2-root.png') !== '')
                        <img class="bt-photo" src="{{ $photo('2-root.png') }}" alt="Site directory set to public">
                    @else
                    <div class="bt-shot">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>Website · Settings · Site directory</b></div>
                        <div class="bt-app">
                            <aside class="bt-side">
                                @foreach($menus as $name)
                                    <div @class(['on' => $name === 'Website'])>{{ $name }}</div>
                                @endforeach
                            </aside>
                            <div class="bt-main">
                                <div class="bt-form">
                                    <div class="bt-line"><span>Domain</span><b class="bt-input">your-domain</b></div>
                                    <div class="bt-line"><span>Site directory</span><b class="bt-input">/www/wwwroot/your-domain</b></div>
                                    <div class="bt-line"><span>Now</span><b class="bt-input is-bad">Run directory /</b></div>
                                    <div class="bt-line">
                                        <span>Run directory</span>
                                        <b class="bt-input is-good bt-hit">/public</b>
                                    </div>
                                    <div><span class="bt-call">Change it to /public, then save</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">Keep the site directory at the project root. The red row is the old run directory. Save the green /public.</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>Step 3 — Paste the rewrite rule</h3>
                    <p>In the same settings window, click Rewrite in the red box. The white box is empty. Paste the whole block below and click the green Save. Leave Current as it is. Without this, every URL except the homepage fails.</p>
                    <div class="code">
                        <button type="button" class="copy" data-copy>Copy</button>
                        <pre><code>location / {
    try_files /html/site_1$uri/index.html /html/site_1$uri $uri $uri/ /index.php?$query_string;
}</code></pre>
                    </div>
                    <p class="hint">Static pages are written under <code>/html/site_1</code>.</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('4-rewrite.png') !== '')
                        <img class="bt-photo" src="{{ $photo('4-rewrite.png') }}" alt="Paste the rewrite rule into the white box and save">
                    @endif
                    <p class="baota-note">The red boxes are Rewrite, the empty rule box, and the green Save. Paste into the white box only.</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>Step 4 — Let two folders be written</h3>
                    <p>On the left, open Files and the site directory. You should see <strong>public</strong> and <strong>storage</strong>. Select the <strong>bootstrap</strong> and <strong>storage</strong> folders. At the top right, open More, then Permission. Keep the owner as <strong>www</strong>, check Apply to subdirectories, and click the green Confirm. Leave the three permission columns as they are. Otherwise the install page will not open.</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('3-perm-open.png') !== '' || $photo('3-perm.png') !== '')
                        @if($photo('3-perm-open.png') !== '')
                            <img class="bt-photo" src="{{ $photo('3-perm-open.png') }}" alt="Select bootstrap and storage, then Permission under More">
                        @endif
                        @if($photo('3-perm.png') !== '')
                            <img class="bt-photo" src="{{ $photo('3-perm.png') }}" alt="Keep owner www, apply to subdirectories, then confirm" style="margin-top:12px">
                        @endif
                    @else
                    <div class="bt-shot">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>Files · Permission</b></div>
                        <div class="bt-app">
                            <aside class="bt-side">
                                @foreach($menus as $name)
                                    <div @class(['on' => $name === 'Files'])>{{ $name }}</div>
                                @endforeach
                            </aside>
                            <div class="bt-main">
                                <div class="bt-form">
                                    <div class="bt-line"><span>Current directory</span><b class="bt-input">/www/wwwroot/your-domain</b></div>
                                    <div class="bt-line"><span>Selected</span><b class="bt-input">storage、bootstrap/cache</b></div>
                                    <div class="bt-line">
                                        <span>Owner</span>
                                        <b class="bt-input is-good bt-hit">www</b>
                                    </div>
                                    <div><span class="bt-call">Set the owner to www and allow writes</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">Change only these two folders. Do not give www the whole disk.</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>Step 5 — Open the install page in a browser</h3>
                    <p>In the address bar, open your domain and add <strong>/install</strong>. The page has three steps: environment, database, and administrator. For a trial, choose the file database. For a real site, choose MySQL and create an empty database under Database on the left first.</p>
                </div>
                <figure class="baota-fig">
                    @if($photo('4-install.png') !== '')
                        <img class="bt-photo" src="{{ $photo('4-install.png') }}" alt="Browser open on /install">
                    @else
                    <div class="bt-shot bt-browser">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>Browser</b></div>
                        <div class="bt-addr">
                            <b class="bt-url bt-hit">http://your-domain/install</b>
                            <span class="bt-call">Type the address like this</span>
                        </div>
                        <div class="bt-main">
                            <div class="bt-form">
                                <div class="bt-line"><span>Step 1</span><b class="bt-input is-good">Environment check</b></div>
                                <div class="bt-line"><span>Step 2</span><b class="bt-input">Database</b></div>
                                <div class="bt-line"><span>Step 3</span><b class="bt-input">Administrator</b></div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">Once the site is on your domain, do not also run php artisan serve.</p>
                </figure>
            </li>
            <li>
                <div class="baota-copy">
                    <h3>Step 6 — Cron, once a minute</h3>
                    <p>At the bottom left, click Cron in the red box, then the green Add task. Keep the type as <strong>Shell script</strong>. Name the task <strong>Site cron</strong>. Do not leave the period on Daily. Change it to <strong>N minutes</strong> and set the number to <strong>1</strong>. Paste only the line below into the script box. Do not add stars, and do not use php-fpm. Then click the green Confirm. Without this task, scheduled collect and cache cleanup never run on their own.</p>
                    <div class="code">
                        <button type="button" class="copy" data-copy>Copy</button>
                        <pre><code>{{ $baotaFill }}</code></pre>
                    </div>
                </div>
                <figure class="baota-fig">
                    @if($photo('5-cron-open.png') !== '' || $photo('5-cron.png') !== '')
                        @if($photo('5-cron-open.png') !== '')
                            <img class="bt-photo" src="{{ $photo('5-cron-open.png') }}" alt="Click Cron on the left, then Add task">
                        @endif
                        @if($photo('5-cron.png') !== '')
                            <img class="bt-photo" src="{{ $photo('5-cron.png') }}" alt="Set the cron to every minute and paste the command" style="margin-top:12px">
                        @endif
                    @else
                    <div class="bt-shot">
                        <div class="bt-chrome"><i></i><i></i><i></i><b>Cron</b></div>
                        <div class="bt-app">
                            <aside class="bt-side">
                                @foreach($menus as $name)
                                    <div @class(['on' => $name === 'Cron'])>{{ $name }}</div>
                                @endforeach
                            </aside>
                            <div class="bt-main">
                                <div class="bt-form">
                                    <div class="bt-line"><span>Task type</span><b class="bt-select is-good bt-hit">Shell script</b></div>
                                    <div class="bt-line"><span>Period</span><b class="bt-select is-good">N minutes · 1</b></div>
                                    <div class="bt-line"><span>Script</span><pre class="bt-script">{{ $baotaFill }}</pre></div>
                                    <div><em class="bt-btn bt-hit">Add task</em><span class="bt-call">Click this when it is filled in</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <p class="baota-note">The command must contain bin/php. If you see php-fpm, switch it to bin/php before adding the task.</p>
                </figure>
            </li>
        </ol>
    </div>
</div>
