<?php

namespace App\Models\Cms;

use App\Models\Concerns\BelongsToSite;
use App\Support\Translatable;
use App\Support\UrlMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Searchable;

class Entry extends Model
{
    use BelongsToSite;
    use Searchable;
    use SoftDeletes;
    use Translatable;

    protected $table = 'cms_entries';

    protected $fillable = [
        'site_id', 'category_id', 'model_id', 'title', 'slug', 'excerpt', 'content',
        'meta_title', 'meta_description', 'attrs', 'cover', 'flags', 'search_text',
        'source_guid', 'collector_id', 'member_id',
        'status', 'sort_order', 'views', 'downloads_count', 'clicks',
        'is_homepage', 'published_at',
        'preview_token', 'preview_expires_at',
        'draft_payload', 'draft_state', 'updated_by', 'locale_sync', 'translation_flags',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'slug' => 'array',
            'excerpt' => 'array',
            'content' => 'array',
            'meta_title' => 'array',
            'meta_description' => 'array',
            'attrs' => 'array',
            'is_homepage' => 'boolean',
            'published_at' => 'datetime',
            'preview_expires_at' => 'datetime',
            'draft_payload' => 'array',
            'locale_sync' => 'array',
            'translation_flags' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Entry $entry): void {
            $indexed = trim((string) $entry->search_text) !== '';
            $sourceChanged = $entry->isDirty(['title', 'excerpt', 'content', 'slug']);
            if ($indexed && ! $sourceChanged) {
                return;
            }
            $entry->rebuildSearchText();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /** 附加栏目（含主栏目，保存时会同步） */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'cms_entry_category', 'entry_id', 'category_id');
    }

    public function modelDef(): BelongsTo
    {
        return $this->belongsTo(ContentModel::class, 'model_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'cms_entry_tag', 'entry_id', 'tag_id');
    }

    /** 主栏目或附加栏目命中 */
    public function scopeInCategories(Builder $query, array $categoryIds): Builder
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $categoryIds))));
        if ($ids === []) {
            return $query->whereRaw('1=0');
        }

        return $query->where(function (Builder $q) use ($ids) {
            $q->whereIn('category_id', $ids)
                ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('cms_categories.id', $ids));
        });
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'entry_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(EntryRevision::class, 'entry_id')->orderByDesc('id');
    }

    public function previewUrl(): ?string
    {
        if (! $this->preview_token) {
            return null;
        }

        return UrlMode::localePrefix().'/preview/'.$this->preview_token;
    }

    public function previewIsValid(): bool
    {
        if (! $this->preview_token) {
            return false;
        }
        if (! $this->preview_expires_at) {
            return true;
        }

        return $this->preview_expires_at->isFuture();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function isLive(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        return ! $this->published_at || $this->published_at->lte(now());
    }

    public function isScheduled(): bool
    {
        return $this->status === 'published'
            && $this->published_at
            && $this->published_at->isFuture();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function hasWorkingDraft(): bool
    {
        return is_array($this->draft_payload) && $this->draft_payload !== [];
    }

    /** 表单和预览读修改稿，不写回已发布字段。 */
    public function overlayWorkingDraft(): void
    {
        $payload = $this->draft_payload;
        if (! is_array($payload) || $payload === []) {
            return;
        }
        foreach (['title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description', 'cover', 'flags', 'attrs', 'category_id', 'sort_order', 'is_homepage'] as $field) {
            if (array_key_exists($field, $payload)) {
                $this->setAttribute($field, $payload[$field]);
            }
        }
        if (isset($payload['category_ids']) && is_array($payload['category_ids'])) {
            $this->setRelation('categories', Category::query()->whereIn('id', array_map('intval', $payload['category_ids']))->get());
        }
        if (isset($payload['tag_ids']) && is_array($payload['tag_ids'])) {
            $this->setRelation('tags', Tag::query()->whereIn('id', array_map('intval', $payload['tag_ids']))->get());
        }
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'published' => admin_text('已发布'),
            'pending' => admin_text('待审'),
            'draft' => admin_text('草稿'),
            default => admin_text($status),
        };
    }

    public function isMemberGated(): bool
    {
        return filter_var($this->attr('member_only', false), FILTER_VALIDATE_BOOLEAN)
            || filter_var($this->attr('require_login', false), FILTER_VALIDATE_BOOLEAN)
            || filter_var($this->attr('islogin', false), FILTER_VALIDATE_BOOLEAN)
            || $this->hasFlag('member');
    }

    /** 游客/无权限会员看不到门槛文档（栏目列表仍可展示，搜索/标签/API/推荐查询会用此范围） */
    public function scopeVisibleToGuests(Builder $query): Builder
    {
        $member = Auth::guard('member')->user();
        if ($member instanceof Member && $member->canMember('view_member_content')) {
            return $query;
        }

        return $query->where(function (Builder $q) {
            $q->where(function (Builder $f) {
                $f->whereNull('flags')
                    ->orWhere('flags', '')
                    ->orWhere(function (Builder $x) {
                        $x->where('flags', '!=', 'member')
                            ->where('flags', 'not like', 'member,%')
                            ->where('flags', 'not like', '%,member,%')
                            ->where('flags', 'not like', '%,member');
                    });
            });
            foreach (['member_only', 'require_login', 'islogin'] as $key) {
                $q->where(function (Builder $a) use ($key) {
                    $a->whereNull('attrs')
                        ->orWhereRaw("json_extract(attrs, '$.{$key}') IS NULL")
                        ->orWhereRaw("json_extract(attrs, '$.{$key}') IN (0, '0', 'false', '')");
                });
            }
        });
    }

    public function scopeHomepage(Builder $query): Builder
    {
        return $query->where('is_homepage', true);
    }

    public function attr(string $key, mixed $default = null): mixed
    {
        return data_get($this->attrs ?? [], $key, $default);
    }

    public function getUrlAttribute(): string
    {
        $prefix = UrlMode::localePrefix();

        $modelSlug = $this->modelDef?->slug;
        if (in_array($modelSlug, ['link'], true)) {
            if (! ($this->attr('url') || $this->attr('source_url'))) {
                return '#';
            }

            return $prefix.'/go/'.$this->id;
        }

        $catSlug = $this->category?->t('slug') ?: 'item';
        $slug = $this->t('slug') ?: $this->id;
        $mode = UrlMode::current();

        if ($this->modelDef?->kind === 'single') {
            return match ($mode) {
                UrlMode::CAT_ID => $prefix.'/c/'.($this->category_id ?: 0),
                UrlMode::MODEL_CAT_ENTRY => $prefix.'/'.($modelSlug ?: 'page').'/'.$catSlug,
                default => $prefix.'/'.$catSlug,
            };
        }

        return match ($mode) {
            UrlMode::ENTRY_ID => $prefix.'/e/'.$this->id,
            UrlMode::CAT_ID => $prefix.'/c/'.($this->category_id ?: 0).'/'.$slug,
            UrlMode::MODEL_CAT_ENTRY => $prefix.'/'.($modelSlug ?: 'item').'/'.$catSlug.'/'.$slug,
            default => $prefix.'/'.$catSlug.'/'.$slug,
        };
    }

    /** 草稿、待审、未到点的定时发布和回收站都不进索引。 */
    public function shouldBeSearchable(): bool
    {
        return ! $this->trashed() && $this->isLive();
    }

    /**
     * Scout 数据库驱动对这些字段做 LIKE。
     * search_text 已含各语言标题、摘要和正文，不含修改稿。
     * site_id 用搜索条件限制，不放进 LIKE，避免数字命中站点 ID。
     *
     * @return array<string, string>
     */
    public function toSearchableArray(): array
    {
        return [
            'search_text' => (string) ($this->search_text ?? ''),
        ];
    }

    public function rebuildSearchText(): void
    {
        $parts = [];
        foreach (['title', 'excerpt', 'content', 'slug'] as $field) {
            $val = $this->{$field};
            if (is_array($val)) {
                $parts = array_merge($parts, array_map('strval', array_values($val)));
            } elseif (is_string($val)) {
                $parts[] = $val;
            }
        }
        $this->search_text = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags(implode(' ', $parts))) ?: ''), 0, 5000);
    }

    public function scopeWithFlag(Builder $query, string $flag): Builder
    {
        $flag = trim($flag);
        if ($flag === '') {
            return $query;
        }

        return $query->where(function (Builder $w) use ($flag) {
            $w->where('flags', $flag)
                ->orWhere('flags', 'like', $flag.',%')
                ->orWhere('flags', 'like', '%,'.$flag.',%')
                ->orWhere('flags', 'like', '%,'.$flag);
        });
    }

    public function hasFlag(string $flag): bool
    {
        $flags = array_filter(array_map('trim', explode(',', (string) $this->flags)));

        return in_array($flag, $flags, true);
    }
}
