<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

/** Literal interface copy only; stored content and user-supplied labels are not translated. */
class WorkbenchI18n
{
    public static function text(string $source, array $replace = []): string
    {
        $locale = app()->getLocale();
        $messages = Lang::get('workbench', [], $locale, false);
        if (AdminUi::normalize($locale) === AdminUi::ZH) {
            $text = is_array($messages) ? ($messages[$source] ?? $source) : $source;
        } else {
            $english = Lang::get('workbench', [], 'en', false);
            $text = (is_array($messages) ? $messages[$source] ?? null : null)
                ?? (is_array($english) ? $english[$source] ?? null : null)
                ?? $source;
        }
        foreach ($replace as $key => $value) {
            $text = str_replace(':'.$key, (string) $value, $text);
        }

        return $text;
    }
}
