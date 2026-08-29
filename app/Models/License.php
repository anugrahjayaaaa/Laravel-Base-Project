<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = [
        'plan_slug', 'license_key', 'type', 'status', 'issued_to',
        'expires_at', 'snapshot', 'revoke_reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'snapshot' => 'array',
    ];

    /** The license is non-transferable: it is bound to the instance it was activated on. */
    public function isActiveAndValid(): bool
    {
        return $this->status === 'active'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
