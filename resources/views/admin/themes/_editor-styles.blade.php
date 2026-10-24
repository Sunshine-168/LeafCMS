@php
    $tplAsset = static function (string $rel): string {
        $path = public_path('assets/vendor/tpl-editor/'.$rel);
        $t = is_file($path) ? (int) filemtime($path) : 1;

        return asset('assets/vendor/tpl-editor/'.$rel).'?v='.$t;
    };
@endphp
<link rel="stylesheet" href="{{ $tplAsset('vendor/codemirror.css') }}">
<link rel="stylesheet" href="{{ $tplAsset('vendor/dialog.css') }}">
<link rel="stylesheet" href="{{ $tplAsset('theme-apple.css') }}">
