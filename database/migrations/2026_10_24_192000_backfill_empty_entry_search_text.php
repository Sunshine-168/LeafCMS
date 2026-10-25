<?php

use App\Models\Cms\Entry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cms_entries') || ! Schema::hasColumn('cms_entries', 'search_text')) {
            return;
        }

        Entry::withoutGlobalScopes()->orderBy('id')->each(function (Entry $entry): void {
            if (trim((string) $entry->search_text) !== '') {
                return;
            }
            $entry->rebuildSearchText();
            $text = (string) $entry->search_text;
            if ($text === '') {
                return;
            }
            DB::table('cms_entries')->where('id', $entry->id)->update([
                'search_text' => $text,
            ]);
        });
    }

    public function down(): void
    {
    }
};
