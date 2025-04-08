<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\_News;
use App\Http\Resources\_Review;
use App\Http\Resources\_Subscription;
use App\Http\Resources\_Award;
use App\Traits\isNullable;

class _User extends JsonResource
{
    use isNullable;
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'nickname' => $this->nickname,
            'prefix' => $this->prefix,
            'phone' => $this->phone,
            'day' => $this->day,
            'month' => $this->month,
            'year' => $this->year,
            'street' => $this->street,
            'street_2' => $this->street_2,
            'psc' => $this->psc,
            'city' => $this->city,
            'country' => $this->country,
            'is_admin' => $this->is_admin,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'news' => _News::collection($this->whenLoaded('news')),
            'reviews' => _Review::collection($this->whenLoaded('reviews')),
            'subscriptions' => _Subscription::collection($this->whenLoaded('subscriptions')),
            'awards' => _Award::collection($this->whenLoaded('awards')),
            'model' => (new \ReflectionClass($this->resource))->getShortName(),
            'products' => _Product::collection($this->whenLoaded('products')),
            'favourites' => _Favourite::collection($this->favourites),
        ];
    }
}
