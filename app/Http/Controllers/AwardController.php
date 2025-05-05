<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Award;
use App\Http\Resources\_Product;
use App\Http\Resources\_UserAward;
use App\Models\Award;
use App\Models\UserAward;
use App\Notifications\NewAwardNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AwardController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();

        // Načítání rekordů uživatele
        $records = $user->records()->with('product')->get()->keyBy('record_type');

        $formattedRecords = [
            'highest_portfolio' => $records->get('highest_portfolio_value') ? $records->get('highest_portfolio_value')->value : 0,
            'most_items' => $records->get('most_items') ? $records->get('most_items')->value : 0,
            'best_purchase' => $records->get('best_purchase') ? [
                'value' => $records->get('best_purchase')->value,
                'product' => new _Product($records->get('best_purchase')->product)
            ] : null,
            'worst_purchase' => $records->get('worst_purchase') ? [
                'value' => $records->get('worst_purchase')->value,
                'product' => new _Product($records->get('worst_purchase')->product)
            ] : null,
        ];


        $userAwards = $user->awards()->get();
        $userAwardIds = $userAwards->pluck('id')->toArray();


        $allAwards = Award::with('conditions')->get();

        foreach ($allAwards as $award) {
            $award->earned = in_array($award->id, $userAwardIds);


            if ($award->earned) {
                $userAward = $userAwards->firstWhere('id', $award->id);
                if ($userAward && isset($userAward->pivot)) {
                    $award->pivot = $userAward->pivot;
                }
            }
        }

        return Inertia::render('Award', [
            'records' => $formattedRecords,
            'awards' => _UserAward::collection($allAwards),
            'earnedAwardsCount' => count($userAwardIds),
            'totalAwardsCount' => $allAwards->count()
        ]);
    }

    // Přidáme metodu do AwardController.php, která zajistí notifikaci při nárokování odznaku

    public function claimBadge(Request $request, Award $award)
    {
        $userId = auth()->id();
        $user = Auth::user();

        $user->awards()->syncWithoutDetaching([$award->id => [
            'user_description' => 'Gratulujeme! Získal jsi odznak ' . $award->name . '!',
            'claimed_at' => now(),
            'earned_at' => now(),
            'notified' => true
        ]]);


        auth()->user()->notify(new NewAwardNotification($award));


        return redirect()->back()->with('success', 'Odznak byl úspěšně nárokován!');
    }
    /*  public function claimBadge(Request $request, Award $award)
    {

        UserAward::where('user_id', auth()->id())
            ->where('award_id', $award->id)
            ->update([
                'user_description' => 'Gratulujeme! Získal jsi odznak ' . $award->name . '!',
                'claimed_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Odznak byl úspěšně nárokován!');
    } */
}
