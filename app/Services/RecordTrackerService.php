<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRecord;
use App\Models\Product;

class RecordTrackerService
{
    /**
     * Aktualizuje všechny rekordy pro daného uživatele.
     *
     * @param User $user Uživatel, jehož rekordy se aktualizují
     * @return array Pole s informacemi o aktualizovaných rekordech
     */
    public function updateUserRecords(User $user): array
    {
        $updated = [];

        // Aktualizace nejvyšší hodnoty portfolia
        $updated['highest_portfolio_value'] = $this->updateHighestPortfolioValue($user);

        // Aktualizace nejvyššího počtu položek
        $updated['most_items'] = $this->updateMostItems($user);

        // Aktualizace nejlepšího a nejhoršího nákupu
        $updated['best_purchase'] = $this->updateBestPurchase($user);
        $updated['worst_purchase'] = $this->updateWorstPurchase($user);

        return $updated;
    }

    /**
     * Aktualizuje rekord pro nejvyšší hodnotu portfolia.
     *
     * @param User $user Uživatel
     * @return bool True, pokud byl rekord aktualizován
     */
    protected function updateHighestPortfolioValue(User $user): bool
    {
        $currentValue = $user->products()->sum('current_value');
        $record = $user->getRecord(UserRecord::TYPE_HIGHEST_PORTFOLIO_VALUE);

        if (!$record) {
            // Vytvoř nový rekord, pokud neexistuje
            $user->records()->create([
                'record_type' => UserRecord::TYPE_HIGHEST_PORTFOLIO_VALUE,
                'value' => $currentValue,
            ]);
            return true;
        } elseif ($currentValue > $record->value) {
            // Aktualizuj existující rekord, pokud je nová hodnota vyšší
            $record->update([
                'value' => $currentValue,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Aktualizuje rekord pro nejvyšší počet položek.
     *
     * @param User $user Uživatel
     * @return bool True, pokud byl rekord aktualizován
     */
    protected function updateMostItems(User $user): bool
    {
        $currentCount = $user->products()->count();
        $record = $user->getRecord(UserRecord::TYPE_MOST_ITEMS);

        if (!$record) {
            // Vytvoř nový rekord, pokud neexistuje
            $user->records()->create([
                'record_type' => UserRecord::TYPE_MOST_ITEMS,
                'count' => $currentCount,
            ]);
            return true;
        } elseif ($currentCount > $record->count) {
            // Aktualizuj existující rekord, pokud je nová hodnota vyšší
            $record->update([
                'count' => $currentCount,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Aktualizuje rekord pro nejlepší nákup (nejvyšší zhodnocení).
     *
     * @param User $user Uživatel
     * @return bool True, pokud byl rekord aktualizován
     */
    protected function updateBestPurchase(User $user): bool
    {
        $bestProduct = $user->products()
            ->selectRaw('*, ((current_value - purchase_price) / purchase_price * 100) as profit_percentage')
            ->whereRaw('purchase_price > 0')  // Vyhni se dělení nulou
            ->orderByDesc('profit_percentage')
            ->first();

        if (!$bestProduct) {
            return false;
        }

        $profitPercentage = (($bestProduct->current_value - $bestProduct->purchase_price) / $bestProduct->purchase_price) * 100;
        $record = $user->getRecord(UserRecord::TYPE_BEST_PURCHASE);

        if (!$record) {
            // Vytvoř nový rekord, pokud neexistuje
            $user->records()->create([
                'record_type' => UserRecord::TYPE_BEST_PURCHASE,
                'product_id' => $bestProduct->id,
                'value' => $bestProduct->current_value,
                'percentage' => $profitPercentage,
            ]);
            return true;
        } elseif (!$record->percentage || $profitPercentage > $record->percentage) {
            // Aktualizuj existující rekord, pokud je nová hodnota vyšší
            $record->update([
                'product_id' => $bestProduct->id,
                'value' => $bestProduct->current_value,
                'percentage' => $profitPercentage,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Aktualizuje rekord pro nejhorší nákup (nejnižší zhodnocení).
     *
     * @param User $user Uživatel
     * @return bool True, pokud byl rekord aktualizován
     */
    protected function updateWorstPurchase(User $user): bool
    {
        $worstProduct = $user->products()
            ->selectRaw('*, ((current_value - purchase_price) / purchase_price * 100) as profit_percentage')
            ->whereRaw('purchase_price > 0')  // Vyhni se dělení nulou
            ->orderBy('profit_percentage')
            ->first();

        if (!$worstProduct) {
            return false;
        }

        $profitPercentage = (($worstProduct->current_value - $worstProduct->purchase_price) / $worstProduct->purchase_price) * 100;
        $record = $user->getRecord(UserRecord::TYPE_WORST_PURCHASE);

        if (!$record) {
            // Vytvoř nový rekord, pokud neexistuje
            $user->records()->create([
                'record_type' => UserRecord::TYPE_WORST_PURCHASE,
                'product_id' => $worstProduct->id,
                'value' => $worstProduct->current_value,
                'percentage' => $profitPercentage,
            ]);
            return true;
        } elseif (!$record->percentage || $profitPercentage < $record->percentage) {
            // Aktualizuj existující rekord, pokud je nová hodnota nižší
            $record->update([
                'product_id' => $worstProduct->id,
                'value' => $worstProduct->current_value,
                'percentage' => $profitPercentage,
            ]);
            return true;
        }

        return false;
    }
}
