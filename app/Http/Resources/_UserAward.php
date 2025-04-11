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
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'category' => $this->category,
            'description' => $this->description,
            'icon' => $this->icon,
            'earned' => $this->earned ?? false,
        ];

        if (isset($this->pivot)) {
            $data['pivot'] = [
                'value' => $this->pivot->value,
                'count' => $this->pivot->count,
                'percentage' => $this->pivot->percentage,
                'notified' => (bool) $this->pivot->notified,
                'earned_at' => $this->pivot->earned_at,
            ];
        } elseif (isset($this->earned_at)) {
            $data['earned_at'] = $this->earned_at;
        }

        return $data;
    }
}
