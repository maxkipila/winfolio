<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _UserAward extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'category' => $this->category,
            'description' => $this->description,
            'icon' => $this->icon,
            'pivot' => [
                'value' => $this->pivot->value,
                'count' => $this->pivot->count,
                'percentage' => $this->pivot->percentage,
                'notified' => (bool) $this->pivot->notified,
                'earned_at' => $this->pivot->earned_at,
            ],
        ];
    }
}
