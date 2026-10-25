<p class="toolbar">
    <a class="btn {{ ($active ?? '') === 'traffic' ? '' : 'btn-muted' }}" href="{{ route('admin.stats.index') }}">{{ admin_text('流量') }}</a>
    <a class="btn {{ ($active ?? '') === 'spiders' ? '' : 'btn-muted' }}" href="{{ route('admin.stats.spiders') }}">{{ admin_text('蜘蛛') }}</a>
    <a class="btn {{ ($active ?? '') === 'logs' ? '' : 'btn-muted' }}" href="{{ route('admin.stats.logs') }}">{{ admin_text('明细') }}</a>
</p>
