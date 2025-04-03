<?php

namespace App\Http\Resources;

use App\Traits\isNullable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _Price extends JsonResource
{
    use isNullable;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'retail' => $this->retail,
            'wholesale' => $this->wholesale,
            'value' => $this->value,
            'condition' => $this->condition,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'product' => new _Product($this->whenLoaded('product')),

        ];
    }
}
