<?php

namespace App\Support;

use Laravel\Scout\Builder;
use Laravel\Scout\Engines\DatabaseEngine;

/**
 * Scout 数据库引擎把关键词直接拼进 LIKE，% 和 _ 会变成通配符。
 * 这里用 ESCAPE 把它们当成普通字符。SQLite 没有默认转义符，只替换反斜杠不够。
 */
class EscapingDatabaseEngine extends DatabaseEngine
{
    protected function addTextSearchConstraints($query, Builder $builder, array $columns, array $prefixColumns = [], array $fullTextColumns = [])
    {
        if (method_exists($builder->model, 'toSearchableEmbedding')) {
            $embeddingColumn = $this->embeddingColumn($builder->model);
            $columns = array_values(array_diff($columns, [$embeddingColumn]));
            $prefixColumns = array_values(array_diff($prefixColumns, [$embeddingColumn]));
            $fullTextColumns = array_values(array_diff($fullTextColumns, [$embeddingColumn]));
        }

        if (blank($builder->query)) {
            return $query;
        }

        $connectionType = $builder->modelConnectionType();
        $likeOperator = $connectionType === 'pgsql' ? 'ilike' : 'like';
        $grammar = $query->getQuery()->getGrammar();

        return $query->where(function ($query) use ($connectionType, $builder, $columns, $prefixColumns, $fullTextColumns, $likeOperator, $grammar) {
            $canSearchPrimaryKey = ctype_digit($builder->query)
                && in_array($builder->model->getKeyType(), ['int', 'integer'], true)
                && ($connectionType !== 'pgsql' || $builder->query <= PHP_INT_MAX)
                && in_array($builder->model->getScoutKeyName(), $columns, true);

            if ($canSearchPrimaryKey) {
                $query->orWhere($builder->model->getQualifiedKeyName(), $builder->query);
            }

            foreach ($columns as $column) {
                if (in_array($column, $fullTextColumns, true)) {
                    continue;
                }
                if ($canSearchPrimaryKey && $column === $builder->model->getScoutKeyName()) {
                    continue;
                }

                $pattern = $this->likePattern((string) $builder->query, in_array($column, $prefixColumns, true));
                $wrapped = $grammar->wrap($builder->model->qualifyColumn($column));
                $query->orWhereRaw($wrapped.' '.$likeOperator." ? escape '!'", [$pattern]);
            }

            if (count($fullTextColumns) > 0) {
                $query->orWhereFullText(
                    array_map(fn ($column) => $builder->model->qualifyColumn($column), $fullTextColumns),
                    $builder->query,
                    $this->getFullTextOptions($builder)
                );
            }
        });
    }

    private function likePattern(string $value, bool $prefixOnly): string
    {
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);

        return $prefixOnly ? $escaped.'%' : '%'.$escaped.'%';
    }
}
