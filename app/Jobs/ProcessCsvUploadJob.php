<?php

namespace App\Jobs;

use App\Domain\AddressValidation\Actions\ProcessCsvUploadAction;
use App\Models\CsvUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCsvUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 hour timeout for large files
    public int $tries = 3; // Retry up to 3 times on failure

    public function __construct(
        public CsvUpload $csvUpload,
        public array $mappings
    ) {}

    /**
     * Handle the job - process CSV upload asynchronously
     */
    public function handle(ProcessCsvUploadAction $processAction): void
    {
        try {
            Log::info('Starting CSV processing job', [
                'upload_id' => $this->csvUpload->id,
                'total_rows' => $this->csvUpload->total_rows,
            ]);

            // Update status to processing
            $this->csvUpload->update([
                'status' => 'processing',
                'processing_started_at' => now(),
            ]);

            // Process the CSV using our action
            $processAction->execute($this->csvUpload, $this->mappings);

            // Mark as completed
            $this->csvUpload->update([
                'status' => 'completed',
                'processed_rows' => $this->csvUpload->total_rows,
                'processing_completed_at' => now(),
            ]);

            Log::info('CSV processing completed', [
                'upload_id' => $this->csvUpload->id,
            ]);

            // TODO: Fire event for completion notification
            // event(new CsvProcessingCompletedEvent($this->csvUpload));

        } catch (\Exception $e) {
            Log::error('CSV processing failed', [
                'upload_id' => $this->csvUpload->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark as failed
            $this->csvUpload->update([
                'status' => 'failed',
            ]);

            // TODO: Fire event for failure notification
            // event(new CsvProcessingFailedEvent($this->csvUpload, $e));

            // Re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CSV processing job failed permanently', [
            'upload_id' => $this->csvUpload->id,
            'error' => $exception->getMessage(),
        ]);

        $this->csvUpload->update([
            'status' => 'failed',
        ]);

        // TODO: Send notification to user about failure
    }
}
