<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\User;
use App\Services\TrendService;
use App\Traits\HasTrends;
use App\Traits\isNullable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class _Product extends JsonResource
{
    use isNullable, HasTrends;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'product_num' => $this->product_num,
            'product_type' => $this->product_type,
            'name'        => $this->name,
            'year'        => $this->year,
            'num_parts'   => $this->num_parts,
            'img_url'     => $this->img_url,
            'theme'       => new _Theme($this->theme),
            'availability' => $this->availability,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'latest_price' => $this->latest_price,

            'annual_growth' => $this->getProductGrowth($this->id, 365),
            'monthly_growth' => $this->getProductGrowth($this->id, 30),
            'weekly_growth' => $this->getProductGrowth($this->id, 7),

            'favourited' => Auth::check() && get_class(Auth::user()) === User::class
                ? Auth::user()->favourites()->where('favourite_type', Product::class)->where('favourite_id', $this->id)->exists()
                : false,
            /*             'favourited' => Auth::user() instanceof User ? Auth::user()->favourites()->where('favourite_type', Product::class)->where('favourite_id', $this->id)->exists() : false, */
            'prices'      => _Price::collection($this->whenLoaded('prices')),
            'model'       => (new \ReflectionClass($this->resource))->getShortName(),
            'review'      => new _Review($this->whenLoaded('review')),
            'subscription' => new _Subscription($this->whenLoaded('subscription')),
            'users'       => _User::collection($this->whenLoaded('users')),
            'reviews'     => _Review::collection($this->whenLoaded('reviews')),
            'news'        => _News::collection($this->whenLoaded('news')),
            'minifigs' => _Product::collection($this->whenLoaded('minifigs')),
            'sets' => _Product::collection($this->whenLoaded('sets')),
        ];
    }
}
