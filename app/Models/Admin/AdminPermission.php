<?php

namespace App\Models\Admin;

use App\Support\AdminUi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminPermission extends Model
{
    protected $table = 'admin_permissions';

    protected $fillable = ['key', 'name', 'group', 'sort'];

    public function displayName(): string
    {
        if (AdminUi::normalize(app()->getLocale()) === AdminUi::ZH) {
            return $this->name;
        }
        if ($this->key === 'publish') {
            return admin_text('发布');
        }
        $key = ['admin_users' => 'admins', 'form_submissions' => 'inbox', 'member_groups' => 'groups', 'html_cache' => 'cache'][$this->key] ?? $this->key;

        return app('translator')->has('admin.nav.'.$key, 'en', false) ? admin_t('nav.'.$key) : $this->name;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_permission', 'permission_id', 'role_id');
    }
}
