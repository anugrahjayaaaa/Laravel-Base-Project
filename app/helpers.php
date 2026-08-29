<?php

use Laravel\Pennant\Feature;

if (! function_exists('feature')) {
    /**
     * Check whether a named feature is enabled (Laravel Pennant).
     * Missing/unknown feature => treated as disabled (fail-closed).
     */
    function feature(string $slug): bool
    {
        // ponytail: fail-closed; an unknown feature is off, never silently on
        return Feature::active($slug);
    }
}

if (! function_exists('featureVisible')) {
    /**
     * Whether a module's menu item should appear in the sidebar.
     * A disabled flag hides the item for everyone (true kill-switch),
     * including feature.manage holders — they reach modules only when on.
     */
    function featureVisible(string $slug): bool
    {
        return feature($slug);
    }
}

if (! function_exists('featureLabel')) {
    /**
     * Human label for a feature slug, from config/pennant.php metadata.
     */
    function featureLabel(string $slug): string
    {
        return config("pennant.features.{$slug}.label", $slug);
    }
}
