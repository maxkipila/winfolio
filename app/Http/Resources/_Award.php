<?php

namespace App\Http\Resources;

use App\Traits\isNullable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _Award extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'category' => $this->category,
            'description' => $this->description,
            'icon' => $this->icon,
            'product_id' => $this->product_id,
            'condition_type' => optional($this->conditions->first())->condition_type,
            'conditions' => _AwardCondition::collection($this->whenLoaded('conditions')),
            /* 'user_records' => _UserRecord::collection($this->whenLoaded('records')), */
            /* 'user_awards' => _UserAward::collection($this->whenLoaded('users')),
 */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
