<?php

namespace App\Models\Cms;

use App\Models\Concerns\BelongsToSite;
use App\Support\CurrentSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CmsFlag extends Model
{
    use BelongsToSite;

    protected $table = 'cms_flags';

    protected $fillable = ['site_id', 'name', 'title', 'sort', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function cacheKey(): string
    {
        return 'cms_flags_options.'.(CurrentSite::idOrDefault() ?? 0);
    }

    /** @return array<string, string> name => title */
    public static function options(): array
    {
        return Cache::remember(self::cacheKey(), 60, function () {
            return static::query()->where('is_active', true)->orderBy('sort')->pluck('title', 'name')->all();
        });
    }

    /** 内置标记按标识码显示。改过的名称保持原文。 */
    public function displayName(): string
    {
        return self::labelFor((string) $this->name, (string) $this->title);
    }

    /** @return array<string, string> */
    public static function displayOptions(): array
    {
        $out = [];
        foreach (self::options() as $name => $title) {
            $out[$name] = self::labelFor((string) $name, (string) $title);
        }

        return $out;
    }

    public static function labelFor(string $name, string $title): string
    {
        $title = trim($title);
        $seeds = [
            'top' => '置顶',
            'recommend' => '推荐',
            'hot' => '热门',
            'headline' => '头条',
            'about' => '关于',
            'live' => '直播',
        ];
        if (($seeds[$name] ?? null) === $title) {
            return admin_t('flag_pack.'.$name);
        }

        return $title !== '' ? $title : $name;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::cacheKey()));
        static::deleted(fn () => Cache::forget(self::cacheKey()));
    }
}
