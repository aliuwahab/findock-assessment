<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ValidationStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total' => $this->total,
            'valid' => $this->valid,
            'invalid' => $this->invalid,
            'error' => $this->error,
            'valid_percentage' => $this->total > 0 ? round(($this->valid / $this->total) * 100, 2) : 0,
            'invalid_percentage' => $this->total > 0 ? round(($this->invalid / $this->total) * 100, 2) : 0,
            'error_percentage' => $this->total > 0 ? round(($this->error / $this->total) * 100, 2) : 0,
        ];
    }
}
