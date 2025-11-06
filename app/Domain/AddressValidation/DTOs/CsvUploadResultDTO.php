<?php

namespace App\Domain\AddressValidation\DTOs;

use App\Models\CsvUpload;

readonly class CsvUploadResultDTO
{
    public function __construct(
        public CsvUpload $upload,
        public string $processingMode,
        public bool $success,
        public string $message,
        public ?string $error = null
    ) {}

    public function isSynchronous(): bool
    {
        return $this->processingMode === 'synchronous';
    }

    public function isAsynchronous(): bool
    {
        return $this->processingMode === 'asynchronous';
    }

    public function hasError(): bool
    {
        return !$this->success;
    }
}
