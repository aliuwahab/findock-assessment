<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ValidationResultResource;
use App\Http\Resources\ValidationStatisticsResource;
use App\Models\CsvUpload;
use App\Support\Filters\AddressValidationFilters;
use App\Support\Filters\FilterBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressValidationController extends ApiController
{
    /**
     * Get all validation results for a CSV upload
     */
    public function results(Request $request, CsvUpload $upload): JsonResponse
    {
        // TODO: Add policy check

        $query = $upload->csvFields();

        $query = FilterBuilder::apply($query, (new AddressValidationFilters($request))->get());

        $results = $query->orderBy('created_at', 'desc')->paginate(50);

        return ValidationResultResource::collection($results);
    }

    /**
     * Get statistics for validation results
     */
    public function statistics(CsvUpload $upload)
    {
        // TODO: Add policy check

        $statistics = (object) [
            'total' => $upload->csvFields()->count(),
            'valid' => $upload->csvFields()->where('validation_status', 'valid')->count(),
            'invalid' => $upload->csvFields()->where('validation_status', 'invalid')->count(),
            'error' => $upload->csvFields()->where('validation_status', 'error')->count(),
        ];

        return new ValidationStatisticsResource($statistics);
    }
}
