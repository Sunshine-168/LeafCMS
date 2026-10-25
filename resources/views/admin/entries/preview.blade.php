@extends('admin.layout')
@section('title', admin_text('已保存内容预览'))
@section('fullwidth', true)
@section('content')
    <div class="preview-toolbar">
        <a class="btn" href="{{ route('admin.entries.edit', $entry) }}">{{ admin_text('返回编辑') }}</a>
        <label>{{ admin_text('预览语言') }}
            <select id="preview-language" aria-label="{{ admin_text('预览语言') }}">
                @foreach(\App\Support\Locale::codesDefaultFirst() as $code)
                    <option value="{{ route('admin.entries.preview.show', ['entry' => $entry, 'locale' => $code]) }}" @selected($locale === $code)>{{ \App\Support\Locale::configured()[$code]['label'] ?? $code }}</option>
                @endforeach
            </select>
        </label>
        <button type="button" class="btn btn-muted" data-preview-width="100%">{{ admin_text('电脑宽度') }}</button>
        <button type="button" class="btn btn-muted" data-preview-width="390px">{{ admin_text('手机宽度') }}</button>
        <a href="{{ $url }}" target="_blank" rel="noopener">{{ admin_text('单独打开') }}</a>
    </div>
    <p class="muted">{{ admin_text('preview_saved_at', ['time' => $entry->updated_at?->format('Y-m-d H:i:s'), 'timezone' => config('app.timezone')]) }}</p>
    <iframe id="content-preview" title="{{ admin_text('已保存的内容预览') }}" src="{{ $url }}" style="display:block;width:100%;height:75vh;margin:0 auto;border:1px solid var(--line);background:white"></iframe>
    <script>
        document.getElementById('preview-language').addEventListener('change', function () { window.location.assign(this.value); });
        document.querySelectorAll('[data-preview-width]').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('content-preview').style.width = button.dataset.previewWidth;
            });
        });
    </script>
@endsection
