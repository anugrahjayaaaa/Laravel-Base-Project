<?php

use App\Models\Feature;

if (! function_exists('feature')) {
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

if (! function_exists('featureVisible')) {
    /**
     * Whether a module's menu item should appear in the sidebar.
     * Visible when enabled, OR when the current user can manage features
     * (they stay reachable so feature.manage holders can use them while off).
     */
    function featureVisible(string $slug): bool
    {
        if (feature($slug)) {
            return true;
        }

        return (bool) optional(auth()->user())->can('feature.manage');
    }
}
