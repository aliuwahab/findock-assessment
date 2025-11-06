<?php

namespace Tests\Unit\Domain\AddressValidation\ValueObjects;

use App\Domain\AddressValidation\ValueObjects\Address;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AddressTest extends TestCase
{
    #[Test]
    public function it_creates_address_from_string()
    {
        $address = Address::fromString('123 Main St, New York, NY');
        
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('123 Main St, New York, NY', $address->toString());
    }

    #[Test]
    public function it_normalizes_address_by_trimming_whitespace()
    {
        $address = new Address('  123 Main St  ');
        
        $this->assertEquals('123 Main St', $address->toString());
    }

    #[Test]
    public function it_throws_exception_for_empty_address()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Address cannot be empty');
        
        new Address('');
    }

    #[Test]
    public function it_throws_exception_for_whitespace_only_address()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Address cannot be empty');
        
        new Address('   ');
    }

    #[Test]
    public function it_throws_exception_for_too_long_address()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Address is too long');
        
        new Address(str_repeat('a', 501));
    }

    #[Test]
    public function it_generates_consistent_hash_for_same_address()
    {
        $address1 = new Address('123 Main St');
        $address2 = new Address('123 Main St');
        
        $this->assertEquals($address1->hash(), $address2->hash());
    }

    #[Test]
    public function it_generates_same_hash_regardless_of_case()
    {
        $address1 = new Address('123 Main St');
        $address2 = new Address('123 MAIN ST');
        
        $this->assertEquals($address1->hash(), $address2->hash());
    }

    #[Test]
    public function it_compares_addresses_correctly()
    {
        $address1 = new Address('123 Main St');
        $address2 = new Address('123 Main St');
        $address3 = new Address('456 Oak Ave');
        
        $this->assertTrue($address1->equals($address2));
        $this->assertFalse($address1->equals($address3));
    }

    #[Test]
    public function it_converts_to_string()
    {
        $address = new Address('123 Main St');
        
        $this->assertEquals('123 Main St', (string) $address);
    }
}
