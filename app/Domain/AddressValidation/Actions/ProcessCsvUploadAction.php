<?php

namespace App\Domain\AddressValidation\Actions;

use App\Domain\AddressValidation\Services\AddressValidationServiceInterface;
use App\Domain\AddressValidation\Services\CsvParserService;
use App\Domain\AddressValidation\ValueObjects\Address;
use App\Models\CsvField;
use App\Models\CsvUpload;
use Illuminate\Support\Facades\Log;

class ProcessCsvUploadAction
{
    public function __construct(
        private CsvParserService $csvParser,
        private AddressValidationServiceInterface $validationService
    ) {}

    public function execute(CsvUpload $csvUpload, array $mappings): void
    {
        $rows = $this->csvParser->parse($csvUpload->file_path);
        
        $existingAddresses = $this->getExistingAddresses($csvUpload->id);
        
        $recordsToInsert = [];
        $processedCount = 0;

        foreach ($rows as $row) {
            $addressField = $this->getAddressFromRow($row, $mappings);
            
            if (empty($addressField)) {
                continue;
            }

            if (in_array($addressField, $existingAddresses)) {
                Log::info("Duplicate address skipped", ['address' => $addressField]);
                continue;
            }

            try {
                $address = Address::fromString($addressField);
                $validationResult = $this->validationService->validate($address);

                $recordsToInsert[] = [
                    'csv_upload_id' => $csvUpload->id,
                    'field_data' => json_encode($this->buildFieldData($row, $mappings)),
                    'validation_status' => $validationResult->status->value,
                    'validation_result' => json_encode($validationResult->toArray()),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $existingAddresses[] = $addressField;
                $processedCount++;

                if (count($recordsToInsert) >= 100) {
                    CsvField::insert($recordsToInsert);
                    $recordsToInsert = [];
                }

            } catch (\Exception $e) {
                Log::error('Error processing row', [
                    'address' => $addressField,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($recordsToInsert)) {
            CsvField::insert($recordsToInsert);
        }

        Log::info("Processed {$processedCount} addresses for CSV upload", [
            'upload_id' => $csvUpload->id,
        ]);
    }

    private function getExistingAddresses(int $csvUploadId): array
    {
        return CsvField::where('csv_upload_id', $csvUploadId)
            ->get()
            ->pluck('field_data')
            ->map(fn($data) => json_decode($data, true)['address'] ?? null)
            ->filter()
            ->toArray();
    }

    private function getAddressFromRow(array $row, array $mappings): ?string
    {
        $addressKey = $mappings['address'] ?? 'address';
        return $row[$addressKey] ?? null;
    }

    private function buildFieldData(array $row, array $mappings): array
    {
        $data = [];
        
        foreach ($mappings as $key => $csvColumn) {
            $data[$key] = $row[$csvColumn] ?? null;
        }
        
        return $data;
    }
}
