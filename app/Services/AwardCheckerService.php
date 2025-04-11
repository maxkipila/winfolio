<?php

namespace App\Services;

use App\Models\Award;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AwardCheckerService
{
    public function checkUserAwards(User $user): array
    {
        $newAwards = [];

        try {
            // Získáme všechny odznaky, které uživatel ještě nemá
            $awards = Award::with('conditions')->get();
            $userAwardIds = $user->awards()->pluck('awards.id')->toArray();
            $awards = $awards->filter(function ($award) use ($userAwardIds) {
                return !in_array($award->id, $userAwardIds);
            });

            /*  \Log::info("Kontrola {$awards->count()} odznaků pro uživatele {$user->id}"); */

            foreach ($awards as $award) {
                $meetsConditions = true;
                $count = null;
                $value = null;
                $percentage = null;

                /*   \Log::info("Kontrola odznaku {$award->name} pro uživatele {$user->id}"); */

                // Zkontrolujeme všechny podmínky odznaku
                foreach ($award->conditions as $condition) {
                    // Získáme hodnotu z enumu
                    $conditionType = $condition->condition_type->value ?? (string)$condition->condition_type;

                    switch ($conditionType) {
                        case 'specific_product':
                            if (!$this->checkSpecificProduct($user, $condition->product_id)) {
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
                        break;  // Pokud jedna podmínka není splněna, přerušíme kontrolu
                    }
                }

                if ($meetsConditions) {
                    /*  \Log::info("Uživatel {$user->id} splňuje podmínky pro odznak {$award->name}, přidělování..."); */

                    try {
                        $user->awards()->attach($award->id, [
                            'earned_at' => now(),
                            'notified' => false,
                            'count' => $count,
                            'value' => $value,
                            'percentage' => $percentage
                        ]);

                        Log::info("Odznak {$award->name} úspěšně přidělen uživateli {$user->id}");
                        $newAwards[] = $award;
                    } catch (\Exception $e) {
                        Log::error("Chyba při přidělování odznaku {$award->name} uživateli {$user->id}: " . $e->getMessage());
                        throw $e;
                    }
                } else {
                    /*   \Log::info("Uživatel {$user->id} nesplňuje podmínky pro odznak {$award->name}"); */
                }
            }
        } catch (\Exception $e) {
            /*  \Log::error("Chyba v checkUserAwards: " . $e->getMessage()); */
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

    // Pomocná metoda pro výpočet procentuálního růstu portfolia


    private function checkAwardConditions(User $user, Award $award): bool
    {
        // Pro každý odznak zkontrolujeme všechny jeho podmínky
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

        // Všechny podmínky byly splněny
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
