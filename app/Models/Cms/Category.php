<?php

namespace App\Models\Cms;

use App\Models\Concerns\BelongsToSite;
use App\Support\Translatable;
use App\Support\UrlMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use BelongsToSite;
    use Translatable;

    protected $table = 'cms_categories';

    protected $fillable = [
        'site_id', 'title', 'slug', 'type', 'model_id', 'parent_id', 'list_tpl', 'show_tpl',
        'category_tpl', 'is_menu', 'url', 'page_size', 'sort_order', 'status',
        'meta_title', 'meta_description', ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'slug' => 'array',
            'meta_title' => 'array',
            'meta_description' => 'array',
            'is_menu' => 'boolean',
        ];
    }

    public function modelDef(): BelongsTo
    {
        return $this->belongsTo(ContentModel::class, 'model_id');
    }

    /**
     * 列表里的栏目名。安装时写入、且中文标题没改过的，按别名显示当前界面语言；改过的仍用库里的标题。
     */
    public function displayTitle(): string
    {
        $slug = trim((string) ($this->translation('slug', 'zh_cn') ?: $this->t('slug')));
        $stored = trim((string) $this->translation('title', 'zh_cn'));
        if ($slug !== '' && (self::seedTitles()[$slug] ?? null) === $stored) {
            return admin_t('category_pack.'.$slug);
        }

        return trim((string) $this->uiTitle());
    }

    /** @return array<string, string> 栏目别名 => 安装时的中文标题 */
    public static function seedTitles(): array
    {
        return [
            'news' => '新闻中心',
            'about' => '关于我们',
            'products' => '产品中心',
            'jobs' => '加入我们',
            'downloads' => '下载中心',
            'nav-tools' => '常用工具',
            'nav-dev' => '开发资源',
            'sources' => '源码市场',
            'blog' => '博客',
            'comics' => '漫画库',
            'chapters' => '章节',
            'matches' => '赛事信号',
            'industry' => '行业资讯',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public static function typeLabel(?string $type): string
    {
        return match ($type) {
            'hub' => '频道',
            'list' => '列表',
            'single' => '单页',
            'link' => '外链',
            default => $type ?: '—',
        };
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->published();
    }

    /** 前台链接（外链栏目用库里的 url） */
    public function frontUrl(): string
    {
        $raw = $this->attributes['url'] ?? null;
        if ($this->type === 'link' && $raw) {
            return (string) $raw;
        }

        $prefix = UrlMode::localePrefix();
        $slug = $this->t('slug');
        $mode = UrlMode::current();

        return match ($mode) {
            UrlMode::CAT_ID => $prefix.'/c/'.$this->id,
            UrlMode::MODEL_CAT_ENTRY => $prefix.'/'.($this->modelDef?->slug ?: 'item').'/'.$slug,
            default => $prefix.'/'.$slug,
        };
    }
}
