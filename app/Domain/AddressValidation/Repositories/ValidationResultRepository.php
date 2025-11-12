<?php

namespace App\Domain\AddressValidation\Repositories;

use App\Models\CsvUpload;
use App\Support\Filters\FilterBuilder;
use App\Support\Filters\FilterCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

class ValidationResultRepository
{
    /**
     * Get paginated validation results with filters applied
     *
     * @param CsvUpload $upload
     * @param FilterCollection $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedResults(CsvUpload $upload, FilterCollection $filters, int $perPage = 50): LengthAwarePaginator
    {
        $query = $upload->csvFields();

        $query = $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get validation statistics for an upload
     *
     * @param CsvUpload $upload
     * @return object
     */
    public function getStatistics(CsvUpload $upload): object
    {
        $stats = $upload->csvFields()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN validation_status = "valid" THEN 1 ELSE 0 END) as valid')
            ->selectRaw('SUM(CASE WHEN validation_status = "invalid" THEN 1 ELSE 0 END) as invalid')
            ->selectRaw('SUM(CASE WHEN validation_status = "error" THEN 1 ELSE 0 END) as error')
            ->first();

        return (object) [
            'total' => (int) $stats->total,
            'valid' => (int) $stats->valid,
            'invalid' => (int) $stats->invalid,
            'error' => (int) $stats->error,
        ];
    }

    /**
     * Apply filters to the query using FilterBuilder
     *
     * @param Builder|Relation $query
     * @param FilterCollection $filters
     * @return Builder|Relation
     */
    private function applyFilters(Builder|Relation $query, FilterCollection $filters): Builder|Relation
    {
        return FilterBuilder::apply($query, $filters);
    }
}
