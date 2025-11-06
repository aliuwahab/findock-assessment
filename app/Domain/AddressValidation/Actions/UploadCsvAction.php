<?php

namespace App\Domain\AddressValidation\Actions;

use App\Domain\AddressValidation\Services\CsvParserService;
use App\Models\CsvUpload;
use Illuminate\Http\UploadedFile;

class UploadCsvAction
{
    public function __construct(
        private CsvParserService $csvParser
    ) {}

    /**
     * Store CSV file and create upload record
     * 
     * NOTE: For production, consider using a private disk with proper access controls
     * and implementing file encryption at rest for sensitive address data.
     */
    public function execute(UploadedFile $file, array $mappings, int $userId): CsvUpload
    {
        // Store file
        $filePath = $file->store('csv_uploads');

        // Estimate row count using Storage path
        $rowCount = $this->csvParser->estimateRowCount($filePath);

        // Create upload record
        $upload = CsvUpload::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'uploaded_by' => $userId,
            'field_mapping' => $mappings,
            'uploaded_at' => now(),
            'status' => 'pending',
            'total_rows' => $rowCount,
        ]);

        // TODO: Fire event for notifications (email, webhooks, etc.)
        // event(new CsvUploadedEvent($upload));
        // This event could trigger:
        // - Email notification to user
        // - Webhook to external system
        // - Slack notification to admin
        // - Analytics tracking

        return $upload;
    }
}
