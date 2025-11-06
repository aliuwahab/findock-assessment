<?php

namespace App\Domain\AddressValidation\Services;

use League\Csv\Reader;
use League\Csv\Exception as CsvException;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;

class CsvParserService
{
    /**
     * Parse CSV file and return iterator for memory-efficient processing
     * Supports both Storage paths and absolute file paths
     */
    public function parse(string $filePath): iterable
    {
        $csv = $this->getCsvReader($filePath);

        try {
            $csv->setHeaderOffset(0); // First row is header
            return $csv->getRecords();
        } catch (CsvException $e) {
            throw new InvalidArgumentException("Invalid CSV file: " . $e->getMessage());
        }
    }

    /**
     * Estimate row count (excluding header)
     */
    public function estimateRowCount(string $filePath): int
    {
        $csv = $this->getCsvReader($filePath);

        try {
            $csv->setHeaderOffset(0);
            return count($csv) - 1; // Exclude header row
        } catch (CsvException $e) {
            throw new InvalidArgumentException("Invalid CSV file: " . $e->getMessage());
        }
    }

    /**
     * Validate CSV structure has required columns
     */
    public function validateStructure(string $filePath, array $requiredColumns): bool
    {
        $csv = $this->getCsvReader($filePath);

        try {
            $csv->setHeaderOffset(0);
            $headers = $csv->getHeader();
            
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $headers)) {
                    throw new InvalidArgumentException("Missing required column: {$column}");
                }
            }
            
            return true;
        } catch (CsvException $e) {
            throw new InvalidArgumentException("Invalid CSV file: " . $e->getMessage());
        }
    }

    /**
     * Get CSV headers
     */
    public function getHeaders(string $filePath): array
    {
        $csv = $this->getCsvReader($filePath);

        try {
            $csv->setHeaderOffset(0);
            return $csv->getHeader();
        } catch (CsvException $e) {
            throw new InvalidArgumentException("Invalid CSV file: " . $e->getMessage());
        }
    }

    /**
     * Get CSV reader from either Storage path or absolute file path
     */
    private function getCsvReader(string $filePath): Reader
    {
        // Check if it's a Storage path (relative path)
        if (!str_starts_with($filePath, '/') && Storage::exists($filePath)) {
            $content = Storage::get($filePath);
            return Reader::createFromString($content);
        }

        // Fall back to absolute file path
        if (file_exists($filePath)) {
            return Reader::createFromPath($filePath, 'r');
        }

        throw new InvalidArgumentException("CSV file not found: {$filePath}");
    }
}
