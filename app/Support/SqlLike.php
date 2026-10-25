<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/** LIKE 里的 % 和 _ 按字面匹配。SQLite 没有默认转义符，反斜杠不够。 */
class SqlLike
{
    public static function contains(string $value): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value).'%';
    }

    public static function whereContains(Builder $query, string $column, string $value, string $boolean = 'and'): Builder
    {
        $wrapped = $query->getQuery()->getGrammar()->wrap($query->qualifyColumn($column));

        return $query->whereRaw($wrapped." like ? escape '!'", [self::contains($value)], $boolean);
    }
}
