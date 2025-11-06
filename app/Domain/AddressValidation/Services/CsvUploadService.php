<?php

namespace App\Domain\AddressValidation\Services;

use App\Domain\AddressValidation\Actions\ProcessCsvUploadAction;
use App\Domain\AddressValidation\Actions\UploadCsvAction;
use App\Domain\AddressValidation\DTOs\CsvUploadResultDTO;
use App\Jobs\ProcessCsvUploadJob;
use App\Models\CsvUpload;
use Illuminate\Http\UploadedFile;

class CsvUploadService
{
    private const SYNC_THRESHOLD = 10;

    public function __construct(
        private readonly UploadCsvAction $uploadAction,
        private readonly ProcessCsvUploadAction $processAction
    ) {}

    /**
     * Upload and process CSV file
     * Returns DTO with processing mode and upload model
     *
     * @param UploadedFile $file
     * @param array $mappings
     * @param int $userId
     * @return CsvUploadResultDTO
     */
    public function uploadAndProcess(UploadedFile $file, array $mappings, int $userId): CsvUploadResultDTO
    {
        $upload = $this->uploadAction->execute($file, $mappings, $userId);

        if ($upload->total_rows <= self::SYNC_THRESHOLD) {
            return $this->processSynchronously($upload, $mappings);
        }

        return $this->processAsynchronously($upload, $mappings);
    }

    /**
     * Process small CSV synchronously
     *
     * @param CsvUpload $upload
     * @param array $mappings
     * @return CsvUploadResultDTO
     */
    private function processSynchronously(CsvUpload $upload, array $mappings): CsvUploadResultDTO
    {
        try {
            $upload->update([
                'status' => 'processing',
                'processing_started_at' => now(),
            ]);

            $this->processAction->execute($upload, $mappings);

            $upload->update([
                'status' => 'completed',
                'processed_rows' => $upload->total_rows,
                'processing_completed_at' => now(),
            ]);

            // TODO: Fire event for completion notification

            return new CsvUploadResultDTO(
                upload: $upload->refresh(),
                processingMode: 'synchronous',
                success: true,
                message: 'CSV processed successfully.'
            );

        } catch (\Exception $e) {
            $upload->update(['status' => 'failed']);

            // TODO: Fire event for failure notification

            return new CsvUploadResultDTO(
                upload: $upload,
                processingMode: 'synchronous',
                success: false,
                message: 'Error processing CSV.',
                error: $e->getMessage()
            );
        }
    }

    /**
     * Process large CSV asynchronously
     *
     * @param CsvUpload $upload
     * @param array $mappings
     * @return CsvUploadResultDTO
     */
    private function processAsynchronously(CsvUpload $upload, array $mappings): CsvUploadResultDTO
    {
        ProcessCsvUploadJob::dispatch($upload, $mappings);

        return new CsvUploadResultDTO(
            upload: $upload,
            processingMode: 'asynchronous',
            success: true,
            message: 'CSV uploaded successfully. Processing in background.'
        );
    }
}
