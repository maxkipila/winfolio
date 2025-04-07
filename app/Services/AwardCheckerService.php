<?php

namespace App\Services;

use App\Models\Award;
use App\Models\AwardCondition;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Collection;

class AwardCheckerService
{

    public function checkUserAwards(User $user): Collection
    {
        $newAwards = collect();

        $userAwardIds = $user->awards()->pluck('awards.id');
        $availableAwards = Award::whereNotIn('id', $userAwardIds)->with('conditions')->get();

        foreach ($availableAwards as $award) {
            // Zkontroluj, zda uživatel splňuje všechny podmínky pro tento odznak
            $conditions = $award->conditions;

            if ($conditions->isEmpty()) {
                continue;
            }

            // každý odznak má pouze jednu podmínku
            $condition = $conditions->first();

            if ($this->checkCondition($user, $condition)) {
                $pivotData = $this->getPivotDataForCondition($user, $condition);
                $user->awards()->attach($award->id, $pivotData);
                $newAwards->push($award);
            }
        }

        return $newAwards;
    }

    protected function checkCondition(User $user, AwardCondition $condition): bool
    {
        switch ($condition->condition_type) {
            case AwardCondition::TYPE_SPECIFIC_PRODUCT:
                // Zkontroluj, zda uživatel má konkrétní produkt
                return $user->products()->where('product_id', $condition->product_id)->exists();

            case AwardCondition::TYPE_SPECIFIC_CATEGORY:
                // Zkontroluj, zda uživatel má jakýkoliv produkt z konkrétní kategorie
                return $user->products()
                    ->whereHas('categories', function ($query) use ($condition) {
                        $query->where('category_id', $condition->category_id);
                    })
                    ->exists();

            case AwardCondition::TYPE_CATEGORY_ITEMS_COUNT:
                // ma uživatel dostatečný počet produktů z konkrétní kategorie
                $count = $user->products()
                    ->whereHas('categories', function ($query) use ($condition) {
                        $query->where('category_id', $condition->category_id);
                    })
                    ->count();
                return $count >= $condition->required_count;

            case AwardCondition::TYPE_TOTAL_ITEMS_COUNT:
                // ma uživatel dostatečný počet všech produktů
                $count = $user->products()->count();
                return $count >= $condition->required_count;

            case AwardCondition::TYPE_PORTFOLIO_VALUE:
                // je hodnota portfolia uživatele je dostatečná
                $value = $user->products()->sum('current_value');
                return $value >= $condition->required_value;

            case AwardCondition::TYPE_PORTFOLIO_PERCENTAGE:
                // je zhodnocení portfolia je dostatečné
                $totalValue = $user->products()->sum('current_value');
                $totalCost = $user->products()->sum('purchase_price');
                if ($totalCost == 0) return false;

                $percentage = (($totalValue - $totalCost) / $totalCost) * 100;
                return $percentage >= $condition->required_percentage;

            default:
                return false;
        }
    }

    protected function getPivotDataForCondition(User $user, AwardCondition $condition): array
    {
        $pivotData = [
            'earned_at' => now(),
            'notified' => false,
        ];

        switch ($condition->condition_type) {
            case AwardCondition::TYPE_SPECIFIC_PRODUCT:
            case AwardCondition::TYPE_SPECIFIC_CATEGORY:

                break;

            case AwardCondition::TYPE_CATEGORY_ITEMS_COUNT:
                $pivotData['count'] = $user->products()
                    ->whereHas('categories', function ($query) use ($condition) {
                        $query->where('category_id', $condition->category_id);
                    })
                    ->count();
                break;

            case AwardCondition::TYPE_TOTAL_ITEMS_COUNT:
                $pivotData['count'] = $user->products()->count();
                break;

            case AwardCondition::TYPE_PORTFOLIO_VALUE:
                $pivotData['value'] = $user->products()->sum('current_value');
                break;

            case AwardCondition::TYPE_PORTFOLIO_PERCENTAGE:
                $totalValue = $user->products()->sum('current_value');
                $totalCost = $user->products()->sum('purchase_price');
                if ($totalCost > 0) {
                    $pivotData['percentage'] = (($totalValue - $totalCost) / $totalCost) * 100;
                    $pivotData['value'] = $totalValue;
                }
                break;
        }

        return $pivotData;
    }
}
