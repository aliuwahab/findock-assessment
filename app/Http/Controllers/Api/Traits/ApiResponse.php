<?php

namespace App\Http\Controllers\Api\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    /**
     * Return a success JSON response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data instanceof JsonResource || $data instanceof ResourceCollection
                ? $data
                : $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error JSON response
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        int $statusCode = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a created resource response (201)
     */
    protected function createdResponse(
        mixed $data,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return an accepted response for async operations (202)
     */
    protected function acceptedResponse(
        mixed $data = null,
        string $message = 'Request accepted for processing'
    ): JsonResponse {
        return $this->successResponse($data, $message, 202);
    }

    /**
     * Return a not found response (404)
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return an unauthorized response (401)
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Return a validation error response (422)
     */
    protected function validationErrorResponse(
        mixed $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return an internal server error response (500)
     */
    protected function serverErrorResponse(
        string $message = 'Internal server error',
        mixed $errors = null
    ): JsonResponse {
        return $this->errorResponse($message, 500, $errors);
    }
}
