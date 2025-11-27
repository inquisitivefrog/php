<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tag_number' => $this->tag_number,
            'breed' => $this->breed,
            'dob' => optional($this->dob)?->toDateString(),
            'weight_kg' => $this->weight_kg !== null ? (float) $this->weight_kg : null,
            'notes' => $this->notes,
            'meta' => $this->meta ?? null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
