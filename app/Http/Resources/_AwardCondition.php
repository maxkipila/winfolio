<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _AwardCondition extends JsonResource
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
            'award_id' => $this->award_id,
            'condition_type' => $this->condition_type,
            'product_id' => $this->product_id,
            'product_name' => $this->when($this->relationLoaded('product'), fn() => $this->product?->name),
            'category_name' => $this->when($this->relationLoaded('category'), fn() => $this->category?->name),
            'category_id' => $this->category_id,
            'product' => $this->when($this->product_id, new _Product($this->whenLoaded('product'))),
            'required_count' => $this->required_count,
            'required_value' => $this->required_value,
            'required_percentage' => $this->required_percentage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
