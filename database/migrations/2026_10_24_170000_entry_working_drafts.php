<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cms_entries')) {
            return;
        }

        Schema::table('cms_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('cms_entries', 'draft_payload')) {
                $table->json('draft_payload')->nullable();
            }
            if (! Schema::hasColumn('cms_entries', 'draft_state')) {
                $table->string('draft_state', 20)->nullable()->index();
            }
            // 只记管理员编号。线上 admin_users.id 和外键类型对不上时，整次迁移会停在这里。
            if (! Schema::hasColumn('cms_entries', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->index();
            }
            if (! Schema::hasColumn('cms_entries', 'locale_sync')) {
                $table->json('locale_sync')->nullable();
            }
            if (! Schema::hasColumn('cms_entries', 'translation_flags')) {
                $table->json('translation_flags')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cms_entries')) {
            return;
        }

        try {
            Schema::table('cms_entries', function (Blueprint $table) {
                $table->dropForeign(['updated_by']);
            });
        } catch (\Throwable) {
            // 没有外键时继续删列
        }

        $drop = array_values(array_filter(
            ['draft_payload', 'draft_state', 'updated_by', 'locale_sync', 'translation_flags'],
            fn (string $column) => Schema::hasColumn('cms_entries', $column),
        ));
        if ($drop === []) {
            return;
        }

        Schema::table('cms_entries', function (Blueprint $table) use ($drop) {
            $table->dropColumn($drop);
        });
    }
};
