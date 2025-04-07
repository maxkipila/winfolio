<?php

namespace App\Services;

use App\Models\User;
use App\Models\Award;
use App\Notifications\AwardEarned;
use Illuminate\Support\Collection;

class AwardNotificationService
{

    public function sendAwardNotifications(User $user, ?Collection $newAwards = null): int
    {
        if (!$newAwards) {
            $newAwards = $user->awards()
                ->wherePivot('notified', false)
                ->get();
        }

        $count = 0;

        foreach ($newAwards as $award) {
            $user->notify(new AwardEarned($award));

            $user->awards()->updateExistingPivot($award->id, [
                'notified' => true
            ]);

            $count++;
        }

        return $count;
    }
}
