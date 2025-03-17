<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _Subscription extends JsonResource
{
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
            'type'      => $this->type,
            'user_id'   => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'user' => new _User($this->whenLoaded('user')),
            'sets' => _Set::collection($this->whenLoaded('sets')),
            'minifigs' => _Minifig::collection($this->whenLoaded('minifigs')),
        ];
    }
}
