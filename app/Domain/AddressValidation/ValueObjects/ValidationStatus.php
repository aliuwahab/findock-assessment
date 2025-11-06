<?php

namespace App\Domain\AddressValidation\ValueObjects;

enum ValidationStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case VALID = 'valid';
    case INVALID = 'invalid';
    case ERROR = 'error';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Validation',
            self::PROCESSING => 'Processing',
            self::VALID => 'Valid Address',
            self::INVALID => 'Invalid Address',
            self::ERROR => 'Validation Error',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::VALID => 'green',
            self::INVALID => 'red',
            self::ERROR => 'yellow',
            self::PENDING => 'gray',
            self::PROCESSING => 'blue',
        };
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::VALID, self::INVALID, self::ERROR]);
    }
}
