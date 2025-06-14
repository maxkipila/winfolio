<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _Category extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'parent_id'       => $this->parent_id,
            'is_subcategory'  => $this->parent_id !== null,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'parent'          => $this->whenLoaded('parent', function () {
                return new self($this->parent);
            }),
            'children'        => self::collection($this->whenLoaded('children')),
        ];
    }
}
