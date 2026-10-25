@extends('admin.layout')
@section('title', admin_t('nav.forms'))
@section('icon', 'clipboard-list')
@section('header_actions')
    <a class="btn btn-muted btn-sm" href="{{ route('admin.form-submissions.index') }}"><i class="fas fa-inbox"></i> {{ admin_text('留言') }}</a>
    <a class="btn btn-sm" href="{{ route('admin.forms.create') }}"><i class="fas fa-plus"></i> {{ admin_text('新建表单') }}</a>
@endsection
@section('content')
    <div class="entry-index form-index">
        @if($forms->isEmpty())
            <div class="list-empty">
                <p>{{ admin_text('还没有表单。') }}</p>
                <p class="muted">{{ admin_text('联系、报名这类前台表单在这里建。主题用标识码调用，提交的内容进留言。') }}</p>
                <a class="btn" href="{{ route('admin.forms.create') }}">{{ admin_text('新建表单') }}</a>
            </div>
        @else
            <p class="muted recycle-lead">{{ admin_text('点名称去改字段。标识码给主题用，例如 @@cmsForm([\'slug\' => \'contact\'])。') }}</p>
            <table class="data">
                <thead>
                <tr>
                    <th>{{ admin_text('表单') }}</th>
                    <th style="width:120px">{{ admin_text('字段') }}</th>
                    <th style="width:140px">{{ admin_text('留言') }}</th>
                    <th class="col-actions">{{ admin_text('操作') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($forms as $form)
                    <tr @class(['is-off' => ! $form->is_active])>
                        <td>
                            <div class="entry-row-title-line">
                                <a class="entry-row-title" href="{{ route('admin.forms.edit', $form) }}">{{ $form->name }}</a>
                                @unless($form->is_active)
                                    <span class="badge badge-off">{{ admin_text('停用') }}</span>
                                @endunless
                            </div>
                            <div class="entry-row-meta">{{ $form->slug }}</div>
                        </td>
                        <td>
                            <a href="{{ route('admin.forms.edit', $form) }}">{{ $form->fieldCount() }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.form-submissions.index', ['form_id' => $form->id]) }}">{{ $form->inboxCount() }}</a>
                        </td>
                        <td class="actions">
                            <form action="{{ route('admin.forms.toggle', $form) }}" method="post">
                                @csrf
                                <button class="btn btn-sm btn-muted" type="submit">{{ $form->is_active ? admin_text('停用') : admin_text('启用') }}</button>
                            </form>
                            <form action="{{ route('admin.forms.destroy', $form) }}" method="post" onsubmit="return confirm(@js(($form->submissions_count ?? 0) > 0 ? admin_text('确定删除「:name」？其中 :count 条留言也会删掉。', ['name' => $form->name, 'count' => $form->submissions_count]) : admin_text('确定删除「:name」？', ['name' => $form->name])))">
                                @csrf @method('DELETE')
                                <button class="btn-quiet" type="submit">{{ admin_text('删除') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="muted entry-index-count">{{ admin_text('共 :count 个', ['count' => $forms->total()]) }}</p>
            {{ $forms->links() }}
        @endif
    </div>
@endsection
