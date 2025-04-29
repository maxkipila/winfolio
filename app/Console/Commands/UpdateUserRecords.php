<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserRecords extends Command
{
    protected $signature = 'app:update-user-records';
    protected $description = 'Aktualizace rekordů uživatelů';

    public function handle()
    {
        $users = User::all();
        foreach ($users as $user) {
            $updated = $this->updateUserRecords($user);
            $this->info("Updated records for user {$user->id}: " . json_encode($updated));
        }
    }

    protected function updateUserRecords(User $user): array
    {
        $updated = [];
        $updated['highest_portfolio_value'] = $this->updateHighestPortfolioValue($user);
        $updated['most_items'] = $this->updateMostItems($user);
        $updated['best_purchase'] = $this->updateBestPurchase($user);
        $updated['worst_purchase'] = $this->updateWorstPurchase($user);
        return $updated;
    }

    protected function updateHighestPortfolioValue(User $user): bool
    {
        $currentValue = $user->products->sum(function ($product) {
            return $product->price ? $product->price->value : 0;
        });

        $record = $user->records()->where('record_type', 'highest_portfolio_value')->first();

        if (!$record || $currentValue > $record->value) {
            $user->records()->updateOrCreate(
                ['record_type' => 'highest_portfolio_value'],
                ['value' => $currentValue]
            );
            return true;
        }
        return false;
    }

    protected function updateMostItems(User $user): bool
    {
        $currentCount = $user->products()->count();
        $record = $user->records()->where('record_type', 'most_items')->first();

        if (!$record || $currentCount > $record->value) {
            $user->records()->updateOrCreate(
                ['record_type' => 'most_items'],
                ['value' => $currentCount]
            );
            return true;
        }
        return false;
    }

    private function updateBestPurchase(User $user): bool
    {
        $products = $user->products()->with('price')->get();
        $bestGrowth = 0;
        $bestProductId = null;

        foreach ($products as $product) {
            if (!$product->pivot->purchase_price || $product->pivot->purchase_price <= 0 || !$product->price || !$product->price->value) {
                continue;
            }

            $purchasePrice = $product->pivot->purchase_price;
            $currentValue = $product->price->value;
            $growth = ($currentValue - $purchasePrice) / $purchasePrice * 100;

            if ($growth > $bestGrowth) {
                $bestGrowth = $growth;
                $bestProductId = $product->id;
            }
        }

        if ($bestProductId) {
            $user->records()->updateOrCreate(
                ['record_type' => 'best_purchase'],
                [
                    'value' => $bestGrowth,
                    'product_id' => $bestProductId
                ]
            );
            return true;
        }
        return false;
    }

    private function updateWorstPurchase(User $user): bool
    {
        $products = $user->products()->with('price')->get();
        $worstGrowth = 0;
        $worstProductId = null;
        $foundNegativeGrowth = false;

        foreach ($products as $product) {
            if (!$product->pivot->purchase_price || $product->pivot->purchase_price <= 0 || !$product->price || !$product->price->value) {
                continue;
            }

            $purchasePrice = $product->pivot->purchase_price;
            $currentValue = $product->price->value;
            $growth = ($currentValue - $purchasePrice) / $purchasePrice * 100;

            if (!$foundNegativeGrowth || $growth < $worstGrowth) {
                $worstGrowth = $growth;
                $worstProductId = $product->id;
                $foundNegativeGrowth = true;
            }
        }

        if ($worstProductId) {
            $user->records()->updateOrCreate(
                ['record_type' => 'worst_purchase'],
                [
                    'value' => $worstGrowth,
                    'product_id' => $worstProductId
                ]
            );
            return true;
        }
        return false;
    }
}
