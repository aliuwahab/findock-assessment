<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pipeline\Pipeline;

class FilterBuilder
{
    /**
     * Apply filters to query builder using Laravel Pipeline
     *
     * @param Builder|Relation $query
     * @param FilterCollection $filters
     * @return Builder|Relation
     */
    public static function apply(Builder|Relation $query, FilterCollection $filters): Builder|Relation
    {
        return app(Pipeline::class)
            ->send($query)
            ->through($filters->all())
            ->thenReturn();
    }
}
