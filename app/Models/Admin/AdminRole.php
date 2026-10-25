<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    protected $table = 'admin_roles';

    protected $fillable = ['name', 'slug', 'is_super', 'remark'];

    protected function casts(): array
    {
        return [
            'is_super' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permission', 'role_id', 'permission_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(AdminUser::class, 'role_id');
    }

    /** 内置角色按标识码显示。改过的名称保持原文，编辑框仍用 name。 */
    public function displayName(): string
    {
        $name = trim((string) $this->name);
        $seed = self::seedCopy($this->slug);
        if ($seed !== null && ($name === '' || $name === $seed['name'])) {
            return admin_t('roles.'.$this->slug);
        }

        return $name;
    }

    /** 内置说明按标识码显示。改过的备注保持原文。 */
    public function displayRemark(): string
    {
        $remark = trim((string) $this->remark);
        if ($remark === '') {
            return '';
        }
        $seed = self::seedCopy($this->slug);
        if ($seed !== null && $remark === $seed['remark']) {
            return admin_t('roles.'.$this->slug.'_remark');
        }

        return $remark;
    }

    /** @return array{name: string, remark: string}|null */
    private static function seedCopy(string $slug): ?array
    {
        return match ($slug) {
            'super_admin' => ['name' => '超级管理员', 'remark' => '拥有全部权限'],
            'editor' => ['name' => '编辑', 'remark' => '内容与运营基础权限'],
            'operator' => ['name' => '运营', 'remark' => '统计与留言处理'],
            default => null,
        };
    }
}
