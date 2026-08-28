<?php

use App\Models\Feature;

if (!function_exists('feature')) {
    /**
     * Check whether a named feature is enabled.
     * Missing feature row => treated as disabled (fail-closed).
     */
    function feature(string $slug): bool
    {
        // ponytail: fail-closed; an absent feature is off, never silently on
        return (bool) Feature::where('slug', $slug)->where('enabled', true)->exists();
    }
}
