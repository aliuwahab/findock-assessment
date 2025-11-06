<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsvField extends Model
{
    use HasFactory;
    protected $fillable = [
        'csv_upload_id',
        'field_data',
        'validation_status',
        'validation_result',
    ];

    protected $casts = [
        'field_data' => 'array',
        'validation_result' => 'array',
    ];

    public function csvUpload()
    {
        return $this->belongsTo(CsvUpload::class);
    }
}
