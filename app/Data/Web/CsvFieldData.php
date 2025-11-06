<?php

namespace App\Data\Web;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CsvFieldData extends Data
{
    public function __construct(
        public int $id,
        public int $csv_upload_id,
        public array $field_data,
        public string $validation_status,
        public ?array $validation_result,
        public string $created_at,
        public string $updated_at,
    ) {}
}
