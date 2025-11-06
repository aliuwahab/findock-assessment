<?php

namespace App\Domain\AddressValidation\ValueObjects;

use InvalidArgumentException;

class Address
{
    private string $value;

    public function __construct(string $address)
    {
        $normalized = trim($address);
        
        if (empty($normalized)) {
            throw new InvalidArgumentException('Address cannot be empty');
        }

        if (strlen($normalized) > 500) {
            throw new InvalidArgumentException('Address is too long (max 500 characters)');
        }

        $this->value = $normalized;
    }

    public static function fromString(string $address): self
    {
        return new self($address);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function hash(): string
    {
        return md5(strtolower($this->value));
    }

    public function equals(Address $other): bool
    {
        return $this->hash() === $other->hash();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
