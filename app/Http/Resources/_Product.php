<?php

namespace App\Http\Resources;

use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use App\Services\TrendService;
use App\Traits\HasTrends;
use App\Traits\isNullable;
use Carbon\Carbon;
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
        $weeklyGrowth = $this->calculateGrowth(7);
        $monthlyGrowth = $this->calculateGrowth(30);
        $yearlyGrowth = $this->calculateGrowth(365);
        $annualGrowth = $this->calculateAnnualGrowth();

        return [
            'id'          => $this->id,
            'product_num' => $this->product_num,
            'product_type' => $this->product_type,
            'name'        => $this->name,
            'year'        => $this->year,
            'num_parts'   => $this->num_parts,
            'img_url'      => $this->getFirstMediaUrl('images') ?: null,
            'theme'       => new _Theme($this->theme),
            'availability' => $this->availability,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'latest_price' => $this->latest_price,
            /*   'images' => $this->images, */
            /* 'annual_growth' => $this->getProductGrowth($this->id, 365),
            'monthly_growth' => $this->getProductGrowth($this->id, 30),
            'weekly_growth' => $this->getProductGrowth($this->id, 7), */
            'growth' => [
                'weekly' => $weeklyGrowth,
                'monthly' => $monthlyGrowth,
                'yearly' => $yearlyGrowth,
                'annual' => $annualGrowth
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
        ];
    }
    private function calculateGrowth(int $days): ?float
    {
        $trendService = app(TrendService::class);
        return $trendService->getProductGrowth($this->id, $days);
    }

    /**
     * Vypočítá roční růst s ošetřením chybějících dat
     */
    private function calculateAnnualGrowth(): ?float
    {
        $trendService = app(TrendService::class);

        $annualGrowth = $trendService->getProductGrowth($this->id, 365);

        if ($annualGrowth !== null) {
            return $annualGrowth;
        }

        $oldestPrice = Price::where('product_id', $this->id)
            ->orderBy('created_at', 'asc')
            ->first();

        $latestPrice = Price::where('product_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$oldestPrice || !$latestPrice || $oldestPrice->id === $latestPrice->id) {
            return null;
        }

        $oldValue = $oldestPrice->value;
        $newValue = $latestPrice->value;

        if ($oldValue <= 0) {
            return null;
        }

        $growthPercentage = (($newValue - $oldValue) / $oldValue) * 100;

        $daysDiff = Carbon::parse($oldestPrice->created_at)->diffInDays(Carbon::parse($latestPrice->created_at));

        if ($daysDiff <= 0) {
            return $growthPercentage;
        }
        $annualizedGrowth = (pow(1 + ($growthPercentage / 100), 365 / $daysDiff) - 1) * 100;

        return round($annualizedGrowth, 2);
    }
}
