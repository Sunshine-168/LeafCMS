<?php

namespace App\Models\Cms;

use App\Models\Admin\AdminUser;
use App\Models\Concerns\BelongsToSite;
use App\Support\AdminLogAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
{
    use BelongsToSite;

    protected $table = 'cms_admin_logs';

    protected $fillable = ['site_id', 'admin_id', 'admin_name', 'method', 'path', 'detail', 'ip', 'status'];

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_id');
    }

    public function actionLabel(): string
    {
        return AdminLogAction::label((string) $this->method, (string) $this->path, $this->status !== null ? (int) $this->status : null);
    }

    /** 操作者。内置演示账号走界面语言，改过的名字保持日志里的原文。 */
    public function actorName(): string
    {
        $user = $this->adminUser;
        if ($user) {
            $shown = $user->displayName();
            if ($shown !== '') {
                return $shown;
            }
        }
        $name = trim((string) $this->admin_name);

        return $name !== '' ? $name : admin_text('未知管理员');
    }

    public function failed(): bool
    {
        return (int) $this->status >= 400;
    }
}
