<?php

namespace App\Support\Filters\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

interface Filter
{
    /**
     * Apply filter to the query builder
     *
     * @param Builder|Relation $query
     * @param Closure $next
     * @return Builder|Relation
     */
    public function handle(Builder|Relation $query, Closure $next): Builder|Relation;
}
