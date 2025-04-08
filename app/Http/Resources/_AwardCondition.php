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
            'product_name' => $this->when($this->relationLoaded('product'), function () {
                return $this->product?->name;
            }),
            'product' => $this->when($this->product_id, new _Product($this->whenLoaded('product'))),
            'category_id' => $this->category_id,
            'category' => $this->when($this->category_id, new _Category($this->whenLoaded('category'))),
            'required_count' => $this->required_count,
            'required_value' => $this->required_value,
            'required_percentage' => $this->required_percentage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
