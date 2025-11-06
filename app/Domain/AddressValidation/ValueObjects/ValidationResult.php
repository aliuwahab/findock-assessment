<?php

namespace App\Domain\AddressValidation\ValueObjects;

class ValidationResult
{
    public function __construct(
        public readonly ValidationStatus $status,
        public readonly ?string $formattedAddress = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?float $confidence = null,
        public readonly ?string $matchType = null,
        public readonly ?array $addressComponents = null,
        public readonly ?string $errorMessage = null,
    ) {}

    public static function valid(
        string $formattedAddress,
        float $latitude,
        float $longitude,
        float $confidence,
        string $matchType,
        array $addressComponents
    ): self {
        return new self(
            status: ValidationStatus::VALID,
            formattedAddress: $formattedAddress,
            latitude: $latitude,
            longitude: $longitude,
            confidence: $confidence,
            matchType: $matchType,
            addressComponents: $addressComponents,
        );
    }

    public static function invalid(): self
    {
        return new self(
            status: ValidationStatus::INVALID,
        );
    }

    public static function error(string $message): self
    {
        return new self(
            status: ValidationStatus::ERROR,
            errorMessage: $message,
        );
    }

    public static function fromGeoapifyResponse(array $response): self
    {
        if (!isset($response['features']) || empty($response['features'])) {
            return self::invalid();
        }

        $feature = $response['features'][0];
        $props = $feature['properties'] ?? [];

        return self::valid(
            formattedAddress: $props['formatted'] ?? '',
            latitude: $props['lat'] ?? 0.0,
            longitude: $props['lon'] ?? 0.0,
            confidence: $props['rank']['confidence'] ?? 0.0,
            matchType: $props['rank']['match_type'] ?? 'unknown',
            addressComponents: [
                'address_line1' => $props['address_line1'] ?? null,
                'address_line2' => $props['address_line2'] ?? null,
                'city' => $props['city'] ?? null,
                'state' => $props['state'] ?? null,
                'postcode' => $props['postcode'] ?? null,
                'country' => $props['country'] ?? null,
                'country_code' => $props['country_code'] ?? null,
            ],
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'formatted_address' => $this->formattedAddress,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'confidence' => $this->confidence,
            'match_type' => $this->matchType,
            'address_components' => $this->addressComponents,
            'error_message' => $this->errorMessage,
        ];
    }

    public function isValid(): bool
    {
        return $this->status === ValidationStatus::VALID;
    }

    public function isInvalid(): bool
    {
        return $this->status === ValidationStatus::INVALID;
    }

    public function hasError(): bool
    {
        return $this->status === ValidationStatus::ERROR;
    }
}
