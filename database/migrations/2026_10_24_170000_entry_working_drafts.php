<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_entries', function (Blueprint $table) {
            $table->json('draft_payload')->nullable();
            $table->string('draft_state', 20)->nullable()->index();
            $table->foreignId('updated_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->json('locale_sync')->nullable();
            $table->json('translation_flags')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cms_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn(['draft_payload', 'draft_state', 'locale_sync', 'translation_flags']);
        });
    }
};
