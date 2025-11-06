<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\AddressValidation\Repositories\CsvUploadRepository;
use App\Domain\AddressValidation\Services\CsvUploadService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\UploadCsvRequest;
use App\Http\Resources\CsvUploadResource;
use App\Models\CsvUpload;
use Illuminate\Http\JsonResponse;

class CsvUploadController extends ApiController
{
    public function __construct(
        private readonly CsvUploadService $uploadService,
        private readonly CsvUploadRepository $repository
    ) {}

    /**
     * Upload CSV file and process addresses
     */
    public function upload(UploadCsvRequest $request): JsonResponse
    {
        $result = $this->uploadService->uploadAndProcess(
            $request->file('file'),
            $request->input('mappings'),
            auth()->id()
        );

        if ($result->hasError()) {
            return $this->serverErrorResponse(
                $result->message,
                ['error' => $result->error]
            );
        }

        $response = [
            'processing_mode' => $result->processingMode,
            'upload' => new CsvUploadResource($result->upload),
        ];

        return $result->isAsynchronous()
            ? $this->acceptedResponse($response, $result->message)
            : $this->successResponse($response, $result->message);
    }

    /**
     * Get upload status (for polling during async processing)
     */
    public function show(CsvUpload $upload): JsonResponse
    {
        // TODO: Add policy check

        return $this->successResponse(
            ['upload' => new CsvUploadResource($upload)]
        );
    }

    /**
     * List user's uploads
     */
    public function index()
    {
        $uploads = $this->repository->getUserUploads(auth()->id());

        return CsvUploadResource::collection($uploads);
    }
}
