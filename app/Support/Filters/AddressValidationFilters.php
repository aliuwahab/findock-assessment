<?php

namespace App\Support\Filters;

use Illuminate\Http\Request;

class AddressValidationFilters
{
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Get the filter collection for address validation
     *
     * @return FilterCollection
     */
    public function get(): FilterCollection
    {
        return new FilterCollection(
            new StatusFilter($this->request),
            new SearchFilter($this->request),
        );
    }
}
