<?php

namespace App\Domain\AddressValidation\DTOs;

use App\Domain\AddressValidation\ValueObjects\ValidationResult;
use Spatie\LaravelData\Data;

class AddressValidationData extends Data
{
    public function __construct(
        public string $originalAddress,
        public string $status,
        public ?string $formattedAddress = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?float $confidence = null,
        public ?string $matchType = null,
        public ?array $addressComponents = null,
        public ?string $errorMessage = null,
    ) {}

    public static function fromValidationResult(string $originalAddress, ValidationResult $result): self
    {
        return new self(
            originalAddress: $originalAddress,
            status: $result->status->value,
            formattedAddress: $result->formattedAddress,
            latitude: $result->latitude,
            longitude: $result->longitude,
            confidence: $result->confidence,
            matchType: $result->matchType,
            addressComponents: $result->addressComponents,
            errorMessage: $result->errorMessage,
        );
    }
}
