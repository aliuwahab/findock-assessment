<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ValidationResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $validationResult = $this->validation_result ?? [];
        
        return [
            'id' => $this->id,
            'original_address' => $this->field_data['address'] ?? null,
            'status' => $this->validation_status,
            'formatted_address' => $validationResult['formatted_address'] ?? null,
            'latitude' => $validationResult['latitude'] ?? null,
            'longitude' => $validationResult['longitude'] ?? null,
            'confidence' => $validationResult['confidence'] ?? null,
            'match_type' => $validationResult['match_type'] ?? null,
            'address_components' => $validationResult['address_components'] ?? null,
            'error_message' => $validationResult['error_message'] ?? null,
            'validated_at' => $this->created_at->toISOString(),
        ];
    }
}
