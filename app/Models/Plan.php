<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['slug', 'name', 'price_monthly', 'is_active', 'limits', 'features'];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'is_active' => 'boolean',
        'limits' => 'array',
        'features' => 'array',
    ];

    /** Convenience accessor for a numeric limit, defaulting to 0. */
    public function limit(string $key, int $default = 0): int
    {
        return (int) ($this->limits[$key] ?? $default);
    }
}
