<?php

namespace App\Domain\AddressValidation\Actions;

use App\Domain\AddressValidation\Services\AddressValidationServiceInterface;
use App\Domain\AddressValidation\ValueObjects\Address;
use App\Domain\AddressValidation\ValueObjects\ValidationResult;

class ValidateAddressAction
{
    public function __construct(
        private AddressValidationServiceInterface $validationService
    ) {}

    public function execute(string $address): ValidationResult
    {
        $addressVO = Address::fromString($address);
        
        return $this->validationService->validate($addressVO);
    }
}
