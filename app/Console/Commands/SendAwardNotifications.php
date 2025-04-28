<?php

namespace App\Console\Commands;

use App\Models\UserAward;
use App\Notifications\NewAwardNotification;
use Illuminate\Console\Command;

class SendAwardNotifications extends Command
{
    protected $signature = 'awards:notify';
    protected $description = 'Odešle notifikace pro neposlané odznaky';

    public function handle()
    {
        $userAwards = UserAward::where('notified', false)->get();
        $count = 0;

        foreach ($userAwards as $userAward) {
            $user = $userAward->user;
            $award = $userAward->award;

            if ($user && $award) {
                $user->notify(new NewAwardNotification($award));
                $userAward->update(['notified' => true]);
                $count++;
            }
        }

        $this->info("Odesláno {$count} notifikací o odznacích.");
        return Command::SUCCESS;
    }
}
