@extends('admin.layout')
@section('title', admin_t('crud.import_models'))
@section('icon', 'file-import')
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.models.index') }}">{{ admin_back('models') }}</a>
@endsection
@section('content')
    <form method="post" class="model-form" action="{{ route('admin.models.import.submit') }}" enctype="multipart/form-data">
        @csrf
        <p class="muted recycle-lead">{{ admin_text('上传由「导出 JSON」生成的文件。相同标识码默认覆盖字段定义，不会删除已有文章。') }}</p>
        <label>{{ admin_text('JSON 文件') }}</label>
        <input type="file" name="file" accept=".json,application/json" required>
        <label class="inline"><input type="checkbox" name="overwrite" value="1" checked>{{ admin_text('标识码已存在时覆盖字段') }}</label>
        <div class="form-actions">
            <button class="btn" type="submit">{{ admin_text('导入') }}</button>
            <a class="btn btn-muted" href="{{ route('admin.models.index') }}">{{ admin_text('取消') }}</a>
        </div>
    </form>
@endsection
