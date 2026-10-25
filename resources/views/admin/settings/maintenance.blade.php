@extends('admin.layout')
@section('title', admin_text('系统维护'))
@section('icon', 'tools')
@section('content')
    <p class="muted recycle-lead">{{ admin_text('仅超级管理员可操作。备份与恢复会替换现在的数据。') }}</p>
    <div class="alert alert-warning">
        <strong>{{ admin_text('恢复会覆盖全系统数据') }}</strong>
        <p>{{ admin_text('包含现在的内容、栏目、账号及数据库设置，并写回备份中的上传文件；不含 .env 服务器配置。恢复前会自动生成一份备份，失败则停止恢复。') }}</p>
    </div>
    <p><a href="{{ route('admin.settings.edit') }}">{{ admin_text('返回网站设置') }}</a> · <a href="{{ route('admin.html-cache.edit') }}">{{ admin_text('页面与模板缓存') }}</a></p>
    @include('admin.settings._maintain')
@endsection
