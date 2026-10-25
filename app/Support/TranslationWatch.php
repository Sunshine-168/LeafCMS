<?php

namespace App\Support;

use App\Models\Cms\Entry;

/** 译文相对默认语言的状态：没写、只写了标题、原文变了译文没跟上。 */
class TranslationWatch
{
    /** @param  array<string, array<string, mixed>>  $before
     * @param  array<string, array<string, mixed>>  $packed */
    public static function remember(Entry $entry, array $before, array $packed): void
    {
        $default = Locale::default();
        $source = self::sourceHash($packed, $default);
        $sync = is_array($entry->locale_sync) ? $entry->locale_sync : [];
        $flags = [];
        foreach (Locale::codes() as $code) {
            if ($code === $default) {
                continue;
            }
            $title = self::text($packed, 'title', $code);
            $body = trim(strip_tags(self::text($packed, 'content', $code)));
            $changed = self::text($before, 'title', $code) !== self::text($packed, 'title', $code)
                || self::text($before, 'content', $code) !== self::text($packed, 'content', $code);
            if ($title === '' && $body === '') {
                $flags[$code] = 'fallback';
                unset($sync[$code]);
            } elseif ($body === '') {
                $flags[$code] = 'missing_body';
                unset($sync[$code]);
            } else {
                if ($changed || ! isset($sync[$code])) {
                    $sync[$code] = $source;
                }
                $flags[$code] = $sync[$code] !== $source ? 'stale' : 'ready';
            }
        }
        $entry->locale_sync = $sync;
        $entry->translation_flags = $flags;
    }

    public static function backfillMissing(): void
    {
        Entry::query()->whereNull('translation_flags')->orderBy('id')->chunkById(100, function ($rows): void {
            foreach ($rows as $entry) {
                $existing = [
                    'title' => is_array($entry->title) ? $entry->title : [],
                    'content' => is_array($entry->content) ? $entry->content : [],
                ];
                self::remember($entry, $existing, $existing);
                $entry->timestamps = false;
                $entry->save();
            }
        });
    }

    /** @return array{fallback:int,missing_body:int,stale:int} */
    public static function counts(): array
    {
        self::backfillMissing();
        $count = function (string $flag): int {
            return Entry::query()->where('translation_flags', 'like', '%"'.$flag.'"%')->count();
        };

        return [
            'fallback' => $count('fallback'),
            'missing_body' => $count('missing_body'),
            'stale' => $count('stale'),
        ];
    }

    /** @param  array<string, array<string, mixed>>  $packed */
    private static function sourceHash(array $packed, string $locale): string
    {
        return hash('sha1', self::text($packed, 'title', $locale).'|'.self::text($packed, 'content', $locale));
    }

    /** @param  array<string, array<string, mixed>>  $packed */
    private static function text(array $packed, string $field, string $locale): string
    {
        $value = $packed[$field][$locale] ?? '';

        return trim((string) $value);
    }
}
