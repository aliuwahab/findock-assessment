<?php

namespace App\Support\Filters\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    /**
     * Apply filter to the query builder
     *
     * @param Builder $query
     * @param Closure $next
     * @return Builder
     */
    public function handle(Builder $query, Closure $next): Builder;
}
