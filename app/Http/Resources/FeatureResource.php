<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug' => $this->slug,
            'label' => $this->label,
            'description' => $this->description,
            'enabled' => (bool) $this->enabled,
        ];
    }
}
