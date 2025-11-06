<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CsvUpload extends Model
{
   use SoftDeletes, HasFactory;

    protected $fillable = [
         'file_name',
         'file_path',
         'uploaded_by',
         'field_mapping',
         'uploaded_at',
         'status',
         'total_rows',
         'processed_rows',
         'processing_started_at',
         'processing_completed_at',
    ];

    protected $casts = [
        'field_mapping' => 'array',
        'uploaded_at' => 'datetime',
        'processing_started_at' => 'datetime',
        'processing_completed_at' => 'datetime',
    ];

    protected $appends = ['progress_percentage'];

    public function getProgressPercentageAttribute(): int
    {
        if (!$this->total_rows || $this->total_rows === 0) {
            return 0;
        }

        return (int) (($this->processed_rows / $this->total_rows) * 100);
    }

    public static $allowedUploadMimeTypes = [
        'text/csv',
        'application/csv',
        'csv',
    ];

    /**
     * Get the CSV fields for this upload
     */
    public function csvFields(): HasMany
    {
        return $this->hasMany(CsvField::class);
    }
}
