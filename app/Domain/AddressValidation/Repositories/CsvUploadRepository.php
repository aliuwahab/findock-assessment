<?php

namespace App\Domain\AddressValidation\Repositories;

use App\Models\CsvUpload;
use Illuminate\Pagination\LengthAwarePaginator;

class CsvUploadRepository
{
    /**
     * Get paginated uploads for a user
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserUploads(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return CsvUpload::where('uploaded_by', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find an upload by ID
     *
     * @param int $id
     * @return CsvUpload|null
     */
    public function find(int $id): ?CsvUpload
    {
        return CsvUpload::find($id);
    }
}
