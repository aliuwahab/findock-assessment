<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\ValidationResultResource;
use App\Models\CsvUpload;
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
        // $this->authorize('view', $upload);

        $query = $upload->csvFields();

        // Filter by validation status
        if ($request->has('status')) {
            $query->where('validation_status', $request->input('status'));
        }

        // Search by address
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereRaw('JSON_EXTRACT(field_data, "$.address") LIKE ?', ["%{$search}%"]);
        }

        $results = $query->orderBy('created_at', 'desc')
            ->paginate(50);

        return $this->successResponse([
            'results' => ValidationResultResource::collection($results),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ]);
    }

    /**
     * Get statistics for validation results
     */
    public function statistics(CsvUpload $upload): JsonResponse
    {
        // TODO: Add policy check
        // $this->authorize('view', $upload);

        $total = $upload->csvFields()->count();
        $valid = $upload->csvFields()->where('validation_status', 'valid')->count();
        $invalid = $upload->csvFields()->where('validation_status', 'invalid')->count();
        $error = $upload->csvFields()->where('validation_status', 'error')->count();

        return $this->successResponse([
            'statistics' => [
                'total' => $total,
                'valid' => $valid,
                'invalid' => $invalid,
                'error' => $error,
                'valid_percentage' => $total > 0 ? round(($valid / $total) * 100, 2) : 0,
                'invalid_percentage' => $total > 0 ? round(($invalid / $total) * 100, 2) : 0,
                'error_percentage' => $total > 0 ? round(($error / $total) * 100, 2) : 0,
            ],
        ]);
    }
}
