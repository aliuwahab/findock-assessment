<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\AddressValidation\Actions\ProcessCsvUploadAction;
use App\Domain\AddressValidation\Actions\UploadCsvAction;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\UploadCsvRequest;
use App\Http\Resources\CsvUploadResource;
use App\Jobs\ProcessCsvUploadJob;
use App\Models\CsvUpload;
use Illuminate\Http\JsonResponse;

class CsvUploadController extends ApiController
{
    private const SYNC_THRESHOLD = 10; // Process synchronously if 10 or fewer addresses

    public function __construct(
        private UploadCsvAction $uploadAction,
        private ProcessCsvUploadAction $processAction
    ) {}

    /**
     * Upload CSV file and process addresses
     * - Synchronous processing for <= 10 addresses (instant results)
     * - Asynchronous processing for > 10 addresses (queued with progress tracking)
     */
    public function upload(UploadCsvRequest $request): JsonResponse
    {
        // Upload file and create record (handled by action)
        $upload = $this->uploadAction->execute(
            $request->file('file'),
            $request->input('mappings'),
            auth()->id()
        );

        // Decide: sync or async based on row count
        if ($upload->total_rows <= self::SYNC_THRESHOLD) {
            return $this->processSynchronously($upload, $request->input('mappings'));
        }

        return $this->processAsynchronously($upload, $request->input('mappings'));
    }

    /**
     * Process small CSV synchronously (instant results)
     */
    private function processSynchronously(CsvUpload $upload, array $mappings): JsonResponse
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
            // event(new CsvProcessingCompletedEvent($upload));

            return $this->successResponse(
                [
                    'processing_mode' => 'synchronous',
                    'upload' => new CsvUploadResource($upload->refresh()),
                ],
                'CSV processed successfully.'
            );

        } catch (\Exception $e) {
            $upload->update(['status' => 'failed']);

            // TODO: Fire event for failure notification
            // event(new CsvProcessingFailedEvent($upload, $e));

            return $this->serverErrorResponse(
                'Error processing CSV.',
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Process large CSV asynchronously (queued)
     */
    private function processAsynchronously(CsvUpload $upload, array $mappings): JsonResponse
    {
        ProcessCsvUploadJob::dispatch($upload, $mappings);

        return $this->acceptedResponse(
            [
                'processing_mode' => 'asynchronous',
                'upload' => new CsvUploadResource($upload),
            ],
            'CSV uploaded successfully. Processing in background.'
        );
    }

    /**
     * Get upload status (for polling during async processing)
     */
    public function show(CsvUpload $upload): JsonResponse
    {
        // TODO: Add policy check
        // $this->authorize('view', $upload);

        return $this->successResponse(
            ['upload' => new CsvUploadResource($upload)]
        );
    }

    /**
     * List user's uploads
     */
    public function index(): JsonResponse
    {
        $uploads = CsvUpload::where('uploaded_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->successResponse([
            'uploads' => CsvUploadResource::collection($uploads),
            'meta' => [
                'current_page' => $uploads->currentPage(),
                'last_page' => $uploads->lastPage(),
                'per_page' => $uploads->perPage(),
                'total' => $uploads->total(),
            ],
        ]);
    }
}
