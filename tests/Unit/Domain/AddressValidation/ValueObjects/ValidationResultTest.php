<?php

namespace Tests\Unit\Domain\AddressValidation\ValueObjects;

use App\Domain\AddressValidation\ValueObjects\ValidationResult;
use App\Domain\AddressValidation\ValueObjects\ValidationStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ValidationResultTest extends TestCase
{
    #[Test]
    public function it_creates_valid_result()
    {
        $result = ValidationResult::valid(
            formattedAddress: '123 Main Street, New York, NY 10001',
            latitude: 40.7128,
            longitude: -74.0060,
            confidence: 0.95,
            matchType: 'full_match',
            addressComponents: [
                'city' => 'New York',
                'state' => 'New York',
                'postcode' => '10001',
            ]
        );

        $this->assertTrue($result->isValid());
        $this->assertFalse($result->isInvalid());
        $this->assertFalse($result->hasError());
        $this->assertEquals(ValidationStatus::VALID, $result->status);
        $this->assertEquals('123 Main Street, New York, NY 10001', $result->formattedAddress);
    }

    #[Test]
    public function it_creates_invalid_result()
    {
        $result = ValidationResult::invalid();

        $this->assertFalse($result->isValid());
        $this->assertTrue($result->isInvalid());
        $this->assertFalse($result->hasError());
        $this->assertEquals(ValidationStatus::INVALID, $result->status);
        $this->assertNull($result->formattedAddress);
    }

    #[Test]
    public function it_creates_error_result()
    {
        $result = ValidationResult::error('API connection failed');

        $this->assertFalse($result->isValid());
        $this->assertFalse($result->isInvalid());
        $this->assertTrue($result->hasError());
        $this->assertEquals(ValidationStatus::ERROR, $result->status);
        $this->assertEquals('API connection failed', $result->errorMessage);
    }

    #[Test]
    public function it_parses_geoapify_response_with_valid_address()
    {
        $apiResponse = [
            'features' => [
                [
                    'properties' => [
                        'formatted' => '1600 Amphitheatre Parkway, Mountain View, CA 94043',
                        'address_line1' => '1600 Amphitheatre Parkway',
                        'city' => 'Mountain View',
                        'state' => 'California',
                        'postcode' => '94043',
                        'country' => 'United States',
                        'country_code' => 'us',
                        'lat' => 37.4224764,
                        'lon' => -122.0842499,
                        'rank' => [
                            'confidence' => 1.0,
                            'match_type' => 'full_match',
                        ],
                    ],
                ],
            ],
        ];

        $result = ValidationResult::fromGeoapifyResponse($apiResponse);

        $this->assertTrue($result->isValid());
        $this->assertEquals('1600 Amphitheatre Parkway, Mountain View, CA 94043', $result->formattedAddress);
        $this->assertEquals(37.4224764, $result->latitude);
        $this->assertEquals(-122.0842499, $result->longitude);
        $this->assertEquals(1.0, $result->confidence);
        $this->assertEquals('full_match', $result->matchType);
        $this->assertEquals('Mountain View', $result->addressComponents['city']);
    }

    #[Test]
    public function it_parses_geoapify_response_with_no_results()
    {
        $apiResponse = [
            'features' => [],
        ];

        $result = ValidationResult::fromGeoapifyResponse($apiResponse);

        $this->assertTrue($result->isInvalid());
        $this->assertNull($result->formattedAddress);
    }

    #[Test]
    public function it_converts_to_array()
    {
        $result = ValidationResult::valid(
            formattedAddress: '123 Main St',
            latitude: 40.7128,
            longitude: -74.0060,
            confidence: 0.95,
            matchType: 'full_match',
            addressComponents: ['city' => 'New York']
        );

        $array = $result->toArray();

        $this->assertEquals('valid', $array['status']);
        $this->assertEquals('123 Main St', $array['formatted_address']);
        $this->assertEquals(40.7128, $array['latitude']);
        $this->assertEquals(-74.0060, $array['longitude']);
        $this->assertEquals(0.95, $array['confidence']);
        $this->assertEquals('full_match', $array['match_type']);
        $this->assertEquals(['city' => 'New York'], $array['address_components']);
    }
}
