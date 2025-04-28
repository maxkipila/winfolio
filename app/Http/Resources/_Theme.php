<?php

namespace App\Http\Resources;

use App\Traits\isNullable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _Theme extends JsonResource
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
            'id'        => $this->id,
            'name'      => $this->name,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'is_subtheme' => $this->parent_id !== null,
            'is_category' => $this->is_category,
            'is_subcategory' => $this->is_subcategory,
            'updated_at' => $this->updated_at,
            'parent'           => new self($this->parent),
            'children'         => self::collection($this->whenLoaded('children')),
            /* 'parent'   => new _Theme($this->whenLoaded('parent')),
            'children' => _Theme::collection($this->whenLoaded('children')), */

        ];
    }
}
