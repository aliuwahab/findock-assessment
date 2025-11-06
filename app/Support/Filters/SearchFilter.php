<?php

namespace App\Support\Filters;

use App\Support\Filters\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchFilter implements Filter
{
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Apply search filter to the query
     *
     * @param Builder $query
     * @param \Closure $next
     * @return Builder
     */
    public function handle(Builder $query, \Closure $next): Builder
    {
        if ($this->request->has('search')) {
            $search = $this->request->input('search');
            $query->whereRaw('JSON_EXTRACT(field_data, "$.address") LIKE ?', ["%{$search}%"]);
        }

        return $next($query);
    }
}
