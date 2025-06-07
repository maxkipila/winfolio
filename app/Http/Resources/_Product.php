<?php

namespace App\Http\Resources;

use App\Models\Admin;
use App\Models\Price;
use App\Models\Product;
use App\Models\Theme;
use App\Models\User;
use App\Services\TrendService;
use App\Traits\HasTrends;
use App\Traits\isNullable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

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
        $trendService = app(TrendService::class);
        $weeklyGrowth = $trendService->getSimpleGrowth($this->id, 7);
        $monthlyGrowth = $trendService->getSimpleGrowth($this->id, 30);
        $yearlyGrowth =  $trendService->getSimpleGrowth($this->id, 365);
        $annualGrowth = $trendService->getAnnualizedGrowth($this->id);

        $userOwns = [];
        $favourited = false;

        $imageUrl = $this->getFirstMediaUrl('images');
        if (empty($imageUrl)) {
            $imageUrl = asset('assets/img/big-user.png'); //fallback image
        }


        if (Auth::check() && !Auth::user() instanceof Admin /* && !Gate::allows('admin') */) {
            $user = Auth::user();

            $userProductRelations = $user->products()
                ->where('products.id', $this->id)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->pivot->id,
                        'product_id' => $product->pivot->product_id,
                        'user_id' => $product->pivot->user_id,
                        'purchase_day' => $product->pivot->purchase_day,
                        'purchase_month' => $product->pivot->purchase_month,
                        'purchase_year' => $product->pivot->purchase_year,
                        'purchase_price' => $product->pivot->purchase_price,
                        'currency' => $product->pivot->currency,
                        'condition' => $product->pivot->condition,
                        'created_at' => $product->pivot->created_at,
                        'updated_at' => $product->pivot->updated_at
                    ];
                })
                ->toArray();

            $userOwns = $userProductRelations;

            // Kontrola oblíbených
            if (method_exists($user, 'favourites')) {
                $favourited = $user->favourites()
                    ->where('favourite_type', Product::class)
                    ->where('favourite_id', $this->id)
                    ->exists();
            }
        }

        return [
            'id'          => $this->id,
            'product_num' => $this->product_num,
            'brickeconomy_id' => $this->brickeconomy_id,
            'product_type' => $this->product_type,
            'name'        => $this->name,
            'year'        => $this->year,
            'num_parts'   => $this->num_parts,
            'img_url'      => $this->getFirstMediaUrl('images') ?: null,
            'theme'       => new _Theme($this->theme),
            'themes' => _Theme::collection($this->themes()->with(['parent'])->get()),
            'availability' => $this->availability,
            'user_owns'   => $userOwns,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'latest_price' => $this->latest_price,
            /*   'images' => $this->images, */
            /* 'annual_growth' => $this->getProductGrowth($this->id, 365),
            'monthly_growth' => $this->getProductGrowth($this->id, 30),
            'weekly_growth' => $this->getProductGrowth($this->id, 7), */
            'growth' => [
                'weekly' => $weeklyGrowth ?? 0,
                'monthly' => $monthlyGrowth ?? 0,
                'yearly' => $yearlyGrowth ?? 0,
                'annual' => $annualGrowth ?? 0
            ],
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
            'images' => $this->images,
            'facts' => $this->facts,
            'used_price' => $this->used_price,
            'used_range' => $this->used_range,
            'released_at' => $this->released_at,
            'prices_count' => $this->prices_count,
            'packaging' => $this->packaging,
        ];
    }
}
