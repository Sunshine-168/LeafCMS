@extends('admin.layout')
@section('title', admin_t('nav.themes'))
@section('icon', 'palette')
@php
    $themes = $themes ?? [];
    $current = $current ?? '';
    $add = $add ?? '';
@endphp
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.settings.edit', ['tab' => 'look']) }}">{{ admin_text('网站外观') }}</a>
    <a class="btn btn-muted btn-sm{{ $add === 'copy' ? ' is-on' : '' }}" href="{{ route('admin.themes.index', ['add' => 'copy']) }}">{{ admin_text('复制一套') }}</a>
    <a class="btn btn-muted btn-sm{{ $add === 'upload' ? ' is-on' : '' }}" href="{{ route('admin.themes.index', ['add' => 'upload']) }}">{{ admin_text('上传 zip') }}</a>
@endsection
@section('content')
    <div class="entry-index theme-index">
<p class="muted recycle-lead">{{ admin_text('主题与模板负责改模板和主题包。改 Logo、颜色、页脚去网站外观。') }}</p>
@php
    $themeLead = e(admin_text('点卡片或「配置」进入这一套：多语言短句、要补的模型和自定义参数。点「编辑模板」改 Blade（自带主题请先复制）。点「用于本站」会补上缺的模型和空栏目（theme.json 里可写中英标题），已有文章和字段不改。自带主题都带语言切换和手机宽度。全站短句在 :phrases。也可以把文件夹放到 :folder 下。换完后还是旧样子，到 :cache 清空。'));
    $themeLead = str_replace(
        [':phrases', ':folder', ':cache'],
        [
            '<a href="'.e(route('admin.locales.index', ['tab' => 'theme'])).'">'.e(admin_text('语言 → 主题文案')).'</a>',
            '<code>resources/views/themes/</code>',
            '<a href="'.e(route('admin.html-cache.edit')).'">'.e(admin_text('缓存')).'</a>',
        ],
        $themeLead
    );
@endphp
<p class="muted recycle-lead">{!! $themeLead !!}</p>

        @if($add === 'copy')
            @php $from = old('from', 'enterprise'); @endphp
            <div class="theme-add">
                <form method="post" action="{{ route('admin.themes.store') }}" class="theme-add-form">
                    @csrf
                    <div class="theme-add-row">
                        <span class="theme-add-k">{{ admin_text('复制') }}</span>
                        <select name="from" aria-label="{{ admin_text('从哪套复制') }}">
                            @foreach($themes as $item)
                                @if($item['complete'])
                                    <option value="{{ $item['slug'] }}" @selected($from === $item['slug'])>{{ $item['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                        <input id="theme-name" type="text" name="name" value="{{ old('name') }}" placeholder="{{ admin_text('显示名称，如 品牌站') }}" required autofocus>
                        <input type="text" name="slug" value="{{ old('slug') }}" placeholder="{{ admin_text('标识 brand') }}" aria-label="{{ admin_text('主题标识') }}" required>
                        <button class="btn" type="submit">{{ admin_text('复制') }}</button>
                        <a class="btn-quiet" href="{{ route('admin.themes.index') }}">{{ admin_text('收起') }}</a>
                    </div>
                </form>
            </div>
        @elseif($add === 'upload')
            <div class="theme-add">
                <form method="post" action="{{ route('admin.themes.upload') }}" enctype="multipart/form-data" class="theme-add-form">
                    @csrf
                    <div class="theme-add-row">
                        <span class="theme-add-k">{{ admin_text('上传') }}</span>
                        <input id="theme-pack" type="file" name="pack" accept=".zip,application/zip" required>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ admin_text('显示名称，可空') }}" aria-label="{{ admin_text('上传后的名称') }}">
                        <input type="text" name="slug" value="{{ old('slug') }}" placeholder="{{ admin_text('标识，可空') }}" aria-label="{{ admin_text('上传后的标识') }}">
                        <button class="btn" type="submit">{{ admin_text('上传') }}</button>
                        <a class="btn-quiet" href="{{ route('admin.themes.index') }}">{{ admin_text('收起') }}</a>
                    </div>
                </form>
            </div>
        @endif

        @if($themes === [])
            <div class="list-empty">
                <p>{{ admin_text('还没有主题。') }}</p>
            </div>
        @else
            <div class="theme-grid">
                @foreach($themes as $item)
                    <article class="theme-card{{ $item['current'] ? ' is-current' : '' }}{{ $item['complete'] ? '' : ' is-off' }}">
                        <a class="theme-card-shot" href="{{ $item['preview_url'] }}" target="_blank" rel="noopener">
                            <img src="{{ $item['preview_url'] }}" alt="{{ admin_text(':name预览', ['name' => $item['name']]) }}">
                        </a>
                        <div class="theme-card-body">
                        <div class="theme-card-head">
                            <div class="theme-card-title"><a href="{{ route('admin.themes.show', $item['slug']) }}">{{ $item['name'] }}</a></div>
                            <div class="theme-card-badges">
                                @if($item['current'])
                                    <span class="badge badge-ok">{{ admin_text('当前使用') }}</span>
                                @endif
                                @if($item['builtin'])
                                    <span class="badge">{{ admin_text('自带') }}</span>
                                @endif
                                @unless($item['complete'])
                                    <span class="badge badge-off">{{ admin_text('缺首页') }}</span>
                                @endunless
                                @if(($item['models'] ?? []) !== [] && empty($item['models_ready']))
                                    <span class="badge badge-off">{{ admin_text('缺模型') }}</span>
                                @endif
                            </div>
                        </div>
                        <p class="theme-card-meta">
                            {{ $item['slug'] }}
                            @if($item['description'] !== '')
                                · {{ $item['description'] }}
                            @endif
                        </p>
                        <p class="theme-card-caps">
                            @if(!empty($item['i18n']))
                                <span>{{ admin_text('多语言') }}</span>
                            @endif
                            @if(!empty($item['responsive']))
                                <span>{{ admin_text('自适应') }}</span>
                            @endif
                        </p>
                        @if(($item['models'] ?? []) !== [])
                            <p class="theme-card-models">{{ admin_text('模型') }} {{ collect($item['models'])->pluck('name')->implode(', ') }}</p>
                        @endif
                        <div class="theme-card-actions">
                            <a class="btn-quiet" href="{{ route('admin.themes.show', $item['slug']) }}">{{ admin_text('配置') }}</a>
                            <a class="btn-quiet" href="{{ route('admin.themes.templates', $item['slug']) }}">{{ admin_text('编辑模板') }}</a>
                            @if($item['current'] && ($item['models'] ?? []) !== [] && empty($item['models_ready']))
                                <form action="{{ route('admin.themes.apply', $item['slug']) }}" method="post">
                                    @csrf
                                    <button class="btn btn-sm" type="submit">{{ admin_text('补上模型') }}</button>
                                </form>
                            @elseif($item['current'])
                                <span class="muted">{{ admin_text('正在用') }}</span>
                            @else
                                <form action="{{ route('admin.themes.apply', $item['slug']) }}" method="post">
                                    @csrf
                                    <button class="btn btn-sm" type="submit" @disabled(! $item['complete'])>{{ admin_text('用于本站') }}</button>
                                </form>
                            @endif
                            @unless($item['builtin'])
                                <form action="{{ route('admin.themes.destroy', $item['slug']) }}" method="post" onsubmit="return confirm(@js(admin_text('去掉「:name」？文件夹会删掉。', ['name' => $item['name']])))">
                                    @csrf @method('DELETE')
                                    <button class="btn-quiet" type="submit" @disabled($item['current'])>{{ admin_text('去掉') }}</button>
                                </form>
                            @endunless
                        </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <p class="muted entry-index-count">{{ admin_text('共 :count 套', ['count' => count($themes)]) }}</p>
        @endif
    </div>
@endsection
