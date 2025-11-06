<?php

namespace App\Domain\AddressValidation\Services;

use App\Domain\AddressValidation\ValueObjects\Address;
use App\Domain\AddressValidation\ValueObjects\ValidationResult;

interface AddressValidationServiceInterface
{
    /**
     * Validate a single address
     */
    public function validate(Address $address): ValidationResult;

    /**
     * Validate multiple addresses (batch processing)
     * 
     * @param Address[] $addresses
     * @return ValidationResult[] Keyed by address hash
     */
    public function validateBatch(array $addresses): array;

    /**
     * Get provider name
     */
    public function getName(): string;

    /**
     * Check if provider is available/configured
     */
    public function isAvailable(): bool;
}
