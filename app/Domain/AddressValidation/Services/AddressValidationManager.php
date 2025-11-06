<?php

namespace App\Domain\AddressValidation\Services;

use Illuminate\Support\Manager;

class AddressValidationManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('address-validation.default_driver', 'geoapify');
    }

    protected function createGeoapifyDriver(): AddressValidationServiceInterface
    {
        $config = $this->config->get('address-validation.drivers.geoapify', []);
        
        return new GeoapifyValidationService($config);
    }

    // Future providers can be added here:
    // protected function createGoogleDriver(): AddressValidationServiceInterface
    // {
    //     $config = $this->config->get('address-validation.drivers.google', []);
    //     return new GoogleMapsValidationService($config);
    // }
}
