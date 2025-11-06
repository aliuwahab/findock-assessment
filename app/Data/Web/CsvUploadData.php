<?php

namespace App\Data\Web;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CsvUploadData extends Data
{
    public function __construct(
        public int $id,
        public string $file_name,
        public int $uploaded_by,
        public string $status,
        public int $total_rows,
        public int $processed_rows,
        public int $progress_percentage,
        public ?string $processing_started_at,
        public ?string $processing_completed_at,
        public ?string $uploaded_at,
        public string $created_at,
        public string $updated_at,
    ) {}
}
