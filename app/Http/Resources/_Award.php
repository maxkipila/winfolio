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
            'id'         => $this->id,
            'name'       => $this->name,
            'type'       => $this->type,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'users'      => _User::collection($this->whenLoaded('users')),
        ];
    }
}
