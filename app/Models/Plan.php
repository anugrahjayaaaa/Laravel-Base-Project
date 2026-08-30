<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    // ponytail: single source for plan limit fields (view + controller read this).
    // Keys kept stable — PlanService::limit() reads max_members / max_projects.
    public const LIMIT_KEYS = [
        'max_members' => 'limit_max_members',
        'max_projects' => 'limit_max_projects',
        'max_storage_mb' => 'limit_max_storage_mb',
    ];

    protected $fillable = [
        'slug', 'name', 'price_monthly', 'billing_period', 'is_active', 'limits', 'features',
    ];

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
