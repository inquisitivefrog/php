<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Cow extends Model
{
    use HasFactory, Searchable;

    // If you prefer UUID primary keys, modify migration & model accordingly.
    protected $fillable = [
        'name',
        'tag_number',
        'breed',
        'dob',
        'weight_kg',
        'notes',
        'meta',
    ];

    protected $casts = [
        'dob' => 'date',
        'weight_kg' => 'decimal:2',
        'meta' => 'array',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tag_number' => $this->tag_number,
            'breed' => $this->breed,
            'dob' => $this->dob?->toDateString(),
            'weight_kg' => $this->weight_kg,
            'notes' => $this->notes,
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'cows';
    }
}
