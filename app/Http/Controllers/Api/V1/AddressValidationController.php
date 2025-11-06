<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\AddressValidation\Repositories\ValidationResultRepository;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ValidationResultResource;
use App\Http\Resources\ValidationStatisticsResource;
use App\Models\CsvUpload;
use App\Support\Filters\AddressValidationFilters;
use Illuminate\Http\Request;

class AddressValidationController extends ApiController
{
    public function __construct(
        private readonly ValidationResultRepository $repository
    ) {}

    /**
     * Get all validation results for a CSV upload
     */
    public function results(Request $request, CsvUpload $upload, AddressValidationFilters $filters)
    {
        // TODO: Add policy check

        $results = $this->repository->getPaginatedResults($upload, $filters->get());

        return ValidationResultResource::collection($results);
    }

    /**
     * Get statistics for validation results
     */
    public function statistics(CsvUpload $upload)
    {
        // TODO: Add policy check

        $statistics = $this->repository->getStatistics($upload);

        return new ValidationStatisticsResource($statistics);
    }
}
