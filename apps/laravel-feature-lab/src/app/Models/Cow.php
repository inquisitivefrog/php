<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cow extends Model
{
    use HasFactory;

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
}
