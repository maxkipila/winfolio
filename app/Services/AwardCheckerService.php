<?php

namespace App\Services;

use App\Models\Award;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewAwardNotification;

class AwardCheckerService
{


    public function checkUserAwards(User $user): array
    {
        $newAwards = [];

        try {
            $awards = Award::with('conditions')->get();
            $userAwardIds = $user->awards()->pluck('awards.id')->toArray();
            $awards = $awards->filter(function ($award) use ($userAwardIds) {
                return !in_array($award->id, $userAwardIds);
            });

            foreach ($awards as $award) {
                $meetsConditions = true;
                $count = null;
                $value = null;
                $percentage = null;


                foreach ($award->conditions as $condition) {
                    $conditionType = $condition->condition_type->value ?? (string)$condition->condition_type;

                    switch ($conditionType) {
                        case 'specific_product':
                            $productId = $condition->product_id ?? null;
                            // Skip or fail safely if no product_id provided
                            if (!$productId || !$this->checkSpecificProduct($user, (int) $productId)) {
                                $meetsConditions = false;
                            }
                            break;

                        case 'total_items_count':
                            $userCount = $user->products()->count();
                            if ($userCount < $condition->required_count) {
                                $meetsConditions = false;
                            } else {
                                $count = $userCount;
                            }
                            break;

                        case 'portfolio_value':
                            $portfolioValue = $this->calculatePortfolioValue($user);
                            if ($portfolioValue < $condition->required_value) {
                                $meetsConditions = false;
                            } else {
                                $value = $portfolioValue;
                            }
                            break;

                        case 'portfolio_percentage':
                            $portfolioGrowth = $this->calculatePortfolioGrowth($user);
                            if ($portfolioGrowth < $condition->required_percentage) {
                                $meetsConditions = false;
                            } else {
                                $percentage = $portfolioGrowth;
                            }
                            break;

                        default:
                            $meetsConditions = false;
                            break;
                    }

                    if (!$meetsConditions) {
                        break;
                    }
                }

                if ($meetsConditions) {
                    try {
                        $user->awards()->attach($award->id, [
                            'earned_at' => now(),
                            'notified' => false,
                            'count' => $count,
                            'value' => $value,
                            'percentage' => $percentage
                        ]);


                        $user->notify(new \App\Notifications\NewAwardNotification($award));


                        $user->awards()->updateExistingPivot($award->id, [
                            'notified' => true
                        ]);

                        $newAwards[] = $award;
                    } catch (\Exception $e) {
                        Log::error("Chyba při přidělování odznaku {$award->name} uživateli {$user->id}: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $newAwards;
    }

    // Pomocná metoda pro výpočet hodnoty portfolia
    private function calculatePortfolioValue(User $user): float
    {
        return $user->products->sum(function ($product) {
            return $product->price ? $product->price->value : 0;
        });
    }



    private function checkAwardConditions(User $user, Award $award): bool
    {
        foreach ($award->conditions as $condition) {
            switch ($condition->condition_type) {
                case 'specific_product':
                    if (!$this->checkSpecificProduct($user, $condition->product_id)) {
                        return false;
                    }
                    break;

                case 'total_items_count':
                    if (!$this->checkTotalProducts($user, $condition->required_count)) {
                        return false;
                    }
                    break;

                case 'portfolio_value':
                    if (!$this->checkPortfolioValue($user, $condition->required_value)) {
                        return false;
                    }
                    break;

                case 'portfolio_percentage':
                    if (!$this->checkPortfolioGrowth($user, $condition->required_percentage)) {
                        return false;
                    }
                    break;

                default:
                    // Neznámý typ podmínky
                    return false;
            }
        }

        return true;
    }

    private function checkSpecificProduct(User $user, int $productId): bool
    {
        return $user->products()->where('products.id', $productId)->exists();
    }

    private function checkSpecificCategory(User $user, int $categoryId, int $requiredCount): bool
    {
        $count = $user->products()
            ->where('theme_id', $categoryId)
            ->count();

        return $count >= $requiredCount;
    }

    private function checkTotalProducts(User $user, int $requiredCount): bool
    {
        return $user->products()->count() >= $requiredCount;
    }

    private function checkPortfolioValue(User $user, float $requiredValue): bool
    {
        $portfolioValue = $user->getPortfolioValue();
        return $portfolioValue >= $requiredValue;
    }

    private function checkPortfolioGrowth(User $user, float $requiredPercentage): bool
    {

        $totalPurchaseValue = $user->products()->sum('purchase_price');

        if ($totalPurchaseValue <= 0) {
            return false;
        }

        $currentValue = $user->getPortfolioValue();
        $growthPercentage = ($currentValue - $totalPurchaseValue) / $totalPurchaseValue * 100;

        return $growthPercentage >= $requiredPercentage;
    }

    private function calculatePortfolioGrowth(User $user): float
    {
        $totalPurchaseValue = 0;
        $currentValue = 0;

        foreach ($user->products as $product) {
            if (isset($product->pivot->purchase_price) && $product->price && $product->price->value) {
                $totalPurchaseValue += $product->pivot->purchase_price;
                $currentValue += $product->price->value;
            }
        }

        if ($totalPurchaseValue <= 0) {
            return 0;
        }

        return (($currentValue - $totalPurchaseValue) / $totalPurchaseValue) * 100;
    }
}
