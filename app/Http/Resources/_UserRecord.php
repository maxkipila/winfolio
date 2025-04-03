<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _UserRecord extends JsonResource
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
            'user_id' => $this->user_id,
            'record_type' => $this->record_type,
            'value' => $this->value,
            'count' => $this->count,
            'percentage' => $this->percentage,
            'product_id' => $this->product_id,
            'product' => $this->when($this->product_id, new _Product($this->whenLoaded('product'))),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
