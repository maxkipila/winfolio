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
    private function updateHighestPortfolioValue(User $user): bool
    {
        // Vypočítání aktuální hodnoty portfolia
        $currentValue = $user->products->sum(function ($product) {
            return $product->price ? $product->price->value : 0;
        });

        // Získání existujícího rekordu, pokud existuje
        $record = $user->records()->where('record_type', 'highest_portfolio_value')->first();

        // Pokud nemáme žádný záznam nebo je nová hodnota vyšší, vytvoříme/aktualizujeme záznam
        if (!$record || $currentValue > $record->value) {
            $user->records()->updateOrCreate(
                ['record_type' => 'highest_portfolio_value'],
                ['value' => $currentValue]
            );
            return true;
        }

        return false;
    }




    private function updateMostItems(User $user): bool
    {
        // Aktuální počet produktů v portfoliu
        $currentCount = $user->products()->count();

        // Získání existujícího rekordu, pokud existuje
        $record = $user->records()->where('record_type', 'most_items')->first();

        // Pokud nemáme žádný záznam nebo je nový počet vyšší, vytvoříme/aktualizujeme záznam
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
        // Získáme všechny produkty uživatele s informacemi o nákupu
        $products = $user->products()->with('price')->get();

        $bestGrowth = 0;
        $bestProductId = null;

        foreach ($products as $product) {
            // Pokud nemáme informace o nákupní ceně nebo aktuální hodnotě, přeskočíme
            if (!$product->pivot->purchase_price || !$product->price || !$product->price->value) {
                continue;
            }

            // Výpočet procentuálního růstu: (aktuální hodnota - nákupní cena) / nákupní cena * 100
            $purchasePrice = $product->pivot->purchase_price;
            $currentValue = $product->price->value;
            $growth = ($currentValue - $purchasePrice) / $purchasePrice * 100;

            if ($growth > $bestGrowth) {
                $bestGrowth = $growth;
                $bestProductId = $product->id;
            }
        }

        // Pokud jsme našli produkt s nejvyšším zhodnocením
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

    /**
     * Aktualizuje rekord pro nejhorší nákup (nejnižší zhodnocení).
     *
     * @param User $user Uživatel
     * @return bool True, pokud byl rekord aktualizován
     */
    private function updateWorstPurchase(User $user): bool
    {
        // Získáme všechny produkty uživatele s informacemi o nákupu
        $products = $user->products()->with('price')->get();

        $worstGrowth = 0;
        $worstProductId = null;
        $foundNegativeGrowth = false;

        foreach ($products as $product) {
            // Pokud nemáme informace o nákupní ceně nebo aktuální hodnotě, přeskočíme
            if (!$product->pivot->purchase_price || !$product->price || !$product->price->value) {
                continue;
            }

            // Výpočet procentuálního růstu: (aktuální hodnota - nákupní cena) / nákupní cena * 100
            $purchasePrice = $product->pivot->purchase_price;
            $currentValue = $product->price->value;
            $growth = ($currentValue - $purchasePrice) / $purchasePrice * 100;

            // Inicializace na první produkt nebo nalezení produktu s horším zhodnocením
            if (!$foundNegativeGrowth || $growth < $worstGrowth) {
                $worstGrowth = $growth;
                $worstProductId = $product->id;
                $foundNegativeGrowth = true;
            }
        }

        // Pokud jsme našli produkt s nejnižším zhodnocením
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
