<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AwardCheckerService;
use Illuminate\Console\Command;

class CheckAwards extends Command
{
    protected $signature = 'app:check-awards {--user_id= : ID konkrétního uživatele pro kontrolu} {--limit= : Omezení počtu zpracovaných uživatelů}';
    protected $description = 'Kontrola odznaků pro všechny nebo konkrétního uživatele';

    protected AwardCheckerService $awardChecker;

    public function __construct(AwardCheckerService $awardChecker)
    {
        parent::__construct();
        $this->awardChecker = $awardChecker;
    }

    public function handle()
    {
        $userId = $this->option('user_id');
        $limit = (int)$this->option('limit');

        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $this->checkAwardsForUser($user);
            } else {
                $this->error("Uživatel s ID {$userId} nebyl nalezen.");
            }
        } else {
            $query = User::query();

            if ($limit > 0) {
                $query->inRandomOrder()->limit($limit);
            }

            $query->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $this->checkAwardsForUser($user);
                }
            });
        }

        return Command::SUCCESS;
    }

    protected function checkAwardsForUser(User $user, bool $debug = false)
    {
        $this->info("Kontroluji odznaky pro uživatele {$user->id} ({$user->email})");
        $this->info("Počet produktů v portfoliu: " . $user->products()->count());

        $portfolioValue = $user->products->sum(function ($product) {
            return $product->price ? $product->price->value : 0;
        });
        $this->info("Hodnota portfolia: " . $portfolioValue);

        try {
            $newAwards = $this->awardChecker->checkUserAwards($user);

            if (count($newAwards) > 0) {
                $this->info("Uživatel získal " . count($newAwards) . " nových odznaků:");

                foreach ($newAwards as $award) {
                    $this->info(" - {$award->name}");
                }
            } else {
                $this->info("Uživatel nezískal žádné nové odznaky.");
            }
        } catch (\Exception $e) {
            $this->error("Chyba při kontrole odznaků: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
