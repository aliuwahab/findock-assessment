<?php

namespace App\Http\Controllers;

use App\Data\Web\CsvFieldData;
use App\Data\Web\CsvUploadData;
use App\Domain\AddressValidation\Actions\ProcessCsvUploadAction;
use App\Domain\AddressValidation\Actions\UploadCsvAction;
use App\Http\Requests\UploadCsvRequest;
use App\Jobs\ProcessCsvUploadJob;
use App\Models\CsvUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CsvUploadController extends Controller
{
    private const SYNC_THRESHOLD = 10;

    public function __construct(
        private UploadCsvAction $uploadAction,
        private ProcessCsvUploadAction $processAction
    ) {}

    /**
     * Display list of uploads
     */
    public function index(): Response
    {
        $uploads = CsvUpload::where('uploaded_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('CsvUploads/Index', [
            'uploads' => [
                'data' => CsvUploadData::collect($uploads->items()),
                'links' => $uploads->linkCollection(),
                'meta' => [
                    'current_page' => $uploads->currentPage(),
                    'from' => $uploads->firstItem(),
                    'last_page' => $uploads->lastPage(),
                    'per_page' => $uploads->perPage(),
                    'to' => $uploads->lastItem(),
                    'total' => $uploads->total(),
                ],
            ],
        ]);
    }

    /**
     * Show upload details with validation results
     */
    public function show(CsvUpload $upload): Response
    {
        // TODO: Add policy check
        // $this->authorize('view', $upload);

        $fields = $upload->csvFields()->paginate(50);

        return Inertia::render('CsvUploads/Show', [
            'upload' => CsvUploadData::from($upload),
            'fields' => [
                'data' => CsvFieldData::collect($fields->items()),
                'links' => $fields->linkCollection(),
                'from' => $fields->firstItem(),
                'to' => $fields->lastItem(),
                'total' => $fields->total(),
            ],
            'statistics' => $this->getStatistics($upload),
        ]);
    }

    /**
     * Upload and process CSV file
     */
    public function upload(UploadCsvRequest $request): RedirectResponse
    {
        $upload = $this->uploadAction->execute(
            $request->file('file'),
            $request->input('mappings'),
            auth()->id()
        );

        // Decide: sync or async
        if ($upload->total_rows <= self::SYNC_THRESHOLD) {
            return $this->processSynchronously($upload, $request->input('mappings'));
        }

        return $this->processAsynchronously($upload, $request->input('mappings'));
    }

    /**
     * Process synchronously
     */
    private function processSynchronously(CsvUpload $upload, array $mappings): RedirectResponse
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

            return redirect()->route('csv-uploads.show', $upload)
                ->with('success', 'CSV processed successfully!');

        } catch (\Exception $e) {
            $upload->update(['status' => 'failed']);

            return back()->with('error', 'Error processing CSV: ' . $e->getMessage());
        }
    }

    /**
     * Process asynchronously
     */
    private function processAsynchronously(CsvUpload $upload, array $mappings): RedirectResponse
    {
        ProcessCsvUploadJob::dispatch($upload, $mappings);

        return redirect()->route('csv-uploads.show', $upload)
            ->with('success', 'CSV uploaded! Processing in background...');
    }

    /**
     * Get statistics for upload
     */
    private function getStatistics(CsvUpload $upload): array
    {
        $total = $upload->csvFields()->count();
        $valid = $upload->csvFields()->where('validation_status', 'valid')->count();
        $invalid = $upload->csvFields()->where('validation_status', 'invalid')->count();
        $error = $upload->csvFields()->where('validation_status', 'error')->count();

        return [
            'total' => $total,
            'valid' => $valid,
            'invalid' => $invalid,
            'error' => $error,
            'valid_percentage' => $total > 0 ? round(($valid / $total) * 100, 2) : 0,
            'invalid_percentage' => $total > 0 ? round(($invalid / $total) * 100, 2) : 0,
            'error_percentage' => $total > 0 ? round(($error / $total) * 100, 2) : 0,
        ];
    }
}
