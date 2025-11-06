<?php

namespace App\Domain\AddressValidation\Services;

use App\Domain\AddressValidation\ValueObjects\Address;
use App\Domain\AddressValidation\ValueObjects\ValidationResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoapifyValidationService implements AddressValidationServiceInterface
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? config('services.geoapify.api_key');
        $this->baseUrl = $config['base_url'] ?? 'https://api.geoapify.com/v1';
        $this->timeout = $config['timeout'] ?? 10;
    }

    public function validate(Address $address): ValidationResult
    {
        // Check cache first
        $cacheKey = $this->getCacheKey($address);
        
        if (Cache::has($cacheKey)) {
            Log::debug('Using cached validation result', ['address' => $address->toString()]);
            return Cache::get($cacheKey);
        }

        // Call API
        $result = $this->callApi($address);

        // Cache successful results
        if ($result->isValid()) {
            Cache::put($cacheKey, $result, now()->addDay());
        }

        return $result;
    }

    public function validateBatch(array $addresses): array
    {
        $results = [];
        
        foreach ($addresses as $address) {
            $results[$address->hash()] = $this->validate($address);
        }

        return $results;
    }

    public function getName(): string
    {
        return 'geoapify';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    private function callApi(Address $address): ValidationResult
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->get('/geocode/search', [
                    'text' => $address->toString(),
                    'apiKey' => $this->apiKey,
                ]);

            if ($response->failed()) {
                return $this->handleFailedResponse($response, $address);
            }

            return ValidationResult::fromGeoapifyResponse($response->json());

        } catch (\Exception $e) {
            Log::error('Unexpected validation error', [
                'address' => $address->toString(),
                'error' => $e->getMessage(),
            ]);

            return ValidationResult::error('Unexpected validation error');
        }
    }

    private function handleFailedResponse($response, Address $address): ValidationResult
    {
        $statusCode = $response->status();

        if ($statusCode === 429) {
            Log::warning('Geoapify rate limit hit', ['address' => $address->toString()]);
            return ValidationResult::error('Rate limit exceeded');
        }

        if ($statusCode >= 500) {
            Log::error('Geoapify server error', [
                'address' => $address->toString(),
                'status' => $statusCode,
            ]);
            return ValidationResult::error('Validation service unavailable');
        }

        Log::error('Geoapify request failed', [
            'address' => $address->toString(),
            'status' => $statusCode,
        ]);

        return ValidationResult::error('Validation request failed');
    }

    private function getCacheKey(Address $address): string
    {
        return 'address_validation:geoapify:' . $address->hash();
    }
}
