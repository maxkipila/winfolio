<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\_News;
use App\Http\Resources\_Review;
use App\Http\Resources\_Subscription;
use App\Http\Resources\_Award;

class _User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (!$this->resource) {
            return [];
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_admin' => $this->is_admin,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'news' => _News::collection($this->whenLoaded('news')),
            'reviews' => _Review::collection($this->whenLoaded('reviews')),
            'subscriptions' => _Subscription::collection($this->whenLoaded('subscriptions')),
            'awards' => _Award::collection($this->whenLoaded('awards')),
        ];
    }
}
