<?php

namespace App\Support\Filters;

use App\Support\Filters\Contracts\Filter;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class StatusFilter implements Filter
{
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Apply status filter to the query
     *
     * @param Builder|Relation $query
     * @param \Closure $next
     * @return Builder|Relation
     */
    public function handle(Builder|Relation $query, \Closure $next): Builder|Relation
    {
        // TODO: Validate that status value is within allowed enum values (valid, invalid, error)
        if ($this->request->has('status')) {
            $query->where('validation_status', $this->request->input('status'));
        }

        return $next($query);
    }
}
