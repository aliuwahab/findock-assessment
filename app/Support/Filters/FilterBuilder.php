<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;

class FilterBuilder
{
    /**
     * Apply filters to query builder using Laravel Pipeline
     *
     * @param Builder $query
     * @param FilterCollection $filters
     * @return Builder
     */
    public static function apply(Builder $query, FilterCollection $filters): Builder
    {
        return app(Pipeline::class)
            ->send($query)
            ->through($filters->all())
            ->thenReturn();
    }
}
