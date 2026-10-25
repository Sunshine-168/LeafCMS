<?php

namespace App\Models\Cms;

use App\Models\Concerns\BelongsToSite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recommend extends Model
{
    use BelongsToSite;

    protected $table = 'cms_recommends';

    protected $fillable = ['site_id', 'name', 'title', 'type', 'query_params', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'query_params' => 'array',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecommendItem::class, 'recommend_id')->orderBy('sort_order');
    }

    /** 内置推荐位按标识码显示。改过的名称保持原文。 */
    public function displayName(): string
    {
        $title = trim((string) $this->title);
        $name = (string) $this->name;
        $seeds = [
            'home_banner' => '首页轮播',
            'partners' => '合作伙伴',
            'hot_links' => '热门导航',
            'live_board' => '今日焦点',
            'latest_news' => '最新新闻（查询型）',
            'home_notice' => '首页公告',
            'home_modules' => '首页能力',
            'home_markets' => '首页市场',
        ];
        if (($seeds[$name] ?? null) === $title) {
            return admin_t('recommend_pack.'.$name);
        }

        return $title !== '' ? $title : $name;
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'image' => '图片',
            'link' => '链接',
            'html' => 'HTML',
            'entry' => '文档',
            'query' => '自动取数',
            default => $type,
        };
    }

    public function listingCount(): string
    {
        if ($this->type === 'query') {
            $n = (int) ($this->query_params['limit'] ?? 10);

            return admin_text('自动最多 :count 篇', ['count' => $n]);
        }
        $n = (int) ($this->items_count ?? $this->items->count());

        return $n > 0 ? admin_text(':count 条', ['count' => $n]) : admin_text('还没有条目');
    }
}
