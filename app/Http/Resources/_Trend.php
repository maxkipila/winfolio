<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _Trend extends JsonResource
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
            'product' => new _Product($this->whenLoaded('product')),
            'type' => $this->type,
            'weekly_growth' => $this->weekly_growth,
            'monthly_growth' => $this->monthly_growth,
            'annual_growth' => $this->annual_growth,
            'favorites_count' => $this->favorites_count,
            'calculated_at' => $this->calculated_at,
        ];
    }
}
