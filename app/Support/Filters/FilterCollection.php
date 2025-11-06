<?php

namespace App\Support\Filters;

use App\Support\Filters\Contracts\Filter;
use InvalidArgumentException;

class FilterCollection
{
    /** @var Filter[] */
    private array $filters = [];

    /**
     * Create a new FilterCollection instance
     *
     * @param Filter ...$filters
     */
    public function __construct(Filter ...$filters)
    {
        $this->filters = $filters;
    }

    /**
     * Create from array of filters
     *
     * @param array $filters
     * @return static
     * @throws InvalidArgumentException
     */
    public static function make(array $filters): static
    {
        foreach ($filters as $filter) {
            if (!$filter instanceof Filter) {
                throw new InvalidArgumentException(
                    sprintf('All filters must implement %s', Filter::class)
                );
            }
        }

        return new static(...$filters);
    }

    /**
     * Get all filters
     *
     * @return Filter[]
     */
    public function all(): array
    {
        return $this->filters;
    }

    /**
     * Add a filter to the collection
     *
     * @param Filter $filter
     * @return $this
     */
    public function add(Filter $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }
}
