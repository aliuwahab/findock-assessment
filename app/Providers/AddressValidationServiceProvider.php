<?php

namespace App\Providers;

use App\Domain\AddressValidation\Services\AddressValidationManager;
use App\Domain\AddressValidation\Services\AddressValidationServiceInterface;
use Illuminate\Support\ServiceProvider;

class AddressValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('address-validation', function ($app) {
            return new AddressValidationManager($app);
        });

        $this->app->bind(AddressValidationServiceInterface::class, function ($app) {
            return $app->make('address-validation')->driver();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
