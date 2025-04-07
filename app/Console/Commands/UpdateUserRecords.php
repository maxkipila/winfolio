<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-user-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        foreach ($users as $user) {
            $updated = $this->updateUserRecords($user);
            $this->info("Updated records for user {$user->id}: " . json_encode($updated));
        }
    }
    public function updateUserRecords(User $user): array
    {
        $updated = [];

        // Aktualizace rekordu pro nejvyšší hodnotu portfolia
        $updated['highest_portfolio_value'] = $this->updateHighestPortfolioValue($user);

        // Aktualizace rekordu pro největší počet položek
        $updated['most_items'] = $this->updateMostItems($user);

        // Aktualizace rekordu pro nejlepší nákup (nejvyšší zhodnocení)
        $updated['best_purchase'] = $this->updateBestPurchase($user);

        // Aktualizace rekordu pro nejhorší nákup (nejnižší zhodnocení)
        $updated['worst_purchase'] = $this->updateWorstPurchase($user);

        return $updated;
    }
}
