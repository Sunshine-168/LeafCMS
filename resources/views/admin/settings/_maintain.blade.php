@php
    $restorePhrase = \App\Support\AdminUi::isChinese()
        ? (\App\Support\AdminUi::current() === 'zh_tw' ? '恢復全部站點' : '恢复全部站点')
        : 'RESTORE ALL SITES';
    $backups = $backups ?? [];
    $sizeLabel = function (int $bytes): string {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format(max($bytes, 0) / 1024, 1).' KB';
    };
@endphp
<div class="html-cache-card">
    <h3>{{ admin_text('备份') }}</h3>
    <p class="muted field-hint">{{ admin_text('备份现在的数据、账号、数据库设置和上传文件，最多留最近 5 份（含恢复前自动备份）。不含 .env 服务器密码配置。') }}</p>
    <div class="html-cache-actions">
        <form method="post" action="{{ route('admin.cache.backup') }}">
            @csrf
            <button class="btn" type="submit">{{ admin_text('立即备份') }}</button>
        </form>
    </div>
    @if($backups === [])
        <p class="muted field-hint" style="margin-top:12px">{{ admin_text('还没有备份。') }}</p>
    @else
        <div class="backup-list">
            @foreach($backups as $b)
                <div class="backup-item">
                    <div class="backup-item-header">
                        <div>
                            <div class="entry-row-title-line">{{ date('Y-m-d H:i', $b['mtime'] ?? 0) }}</div>
                            <div class="entry-row-meta">{{ $sizeLabel((int) ($b['size'] ?? 0)) }} · {{ $b['file'] }}</div>
                        </div>
                        <a class="btn btn-muted btn-sm" href="{{ route('admin.cache.backup.download', $b['file']) }}">{{ admin_text('下载') }}</a>
                    </div>
                    <details class="backup-restore">
                        <summary>{{ admin_text('恢复此备份') }}</summary>
                        <p class="muted">{{ admin_text('会覆盖现在的数据，恢复前将自动备份当前数据。请核对上方备份时间。') }}</p>
                        <form method="post" action="{{ route('admin.cache.backup.restore', $b['file']) }}" onsubmit="return confirm(@js(admin_text('restore_confirm')))">
                            @csrf
                            <label for="restore-{{ $loop->index }}">{{ admin_text('restore_prompt', ['phrase' => $restorePhrase]) }}</label>
                            <div class="backup-restore-controls">
                                <input id="restore-{{ $loop->index }}" type="text" name="confirmation" required pattern="{{ $restorePhrase }}" autocomplete="off" placeholder="{{ $restorePhrase }}" aria-label="{{ admin_text('restore_prompt', ['phrase' => $restorePhrase]) }} · {{ $b['file'] }}">
                                <button class="btn btn-muted" type="submit">{{ admin_text('确认还原') }}</button>
                            </div>
                        </form>
                    </details>
                </div>
            @endforeach
        </div>
    @endif
</div>

@if($canSeedDemo ?? false)
<div class="html-cache-card">
    <h3>{{ admin_text('演示数据') }}</h3>
    <p class="muted field-hint">{{ admin_text('写入示例栏目、文章和账号，方便试用。已经有的示例会被更新。正式上线的站点不要点。') }}</p>
    <div class="html-cache-actions">
        <form method="post" action="{{ route('admin.cache.seed-demo') }}" onsubmit="return confirm(@js(admin_text('demo_confirm')))">
            @csrf
            <button class="btn btn-muted" type="submit">{{ admin_text('导入演示数据') }}</button>
        </form>
    </div>
</div>
@else
    <p class="muted">{{ admin_text('正式环境已禁用演示数据导入。') }}</p>
@endif
