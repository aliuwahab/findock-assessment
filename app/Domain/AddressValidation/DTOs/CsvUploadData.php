<?php

namespace App\Domain\AddressValidation\DTOs;

use Spatie\LaravelData\Data;

class CsvUploadData extends Data
{
    public function __construct(
        public string $fileName,
        public string $filePath,
        public int $uploadedBy,
        public array $fieldMapping,
    ) {}
}
