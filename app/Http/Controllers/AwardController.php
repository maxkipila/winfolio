<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Award;
use App\Http\Resources\_Product;
use App\Http\Resources\_UserAward;
use App\Models\Award;
use App\Models\UserAward;
use Illuminate\Http\Request;
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

        // Získání odznaků, které uživatel má
        $userAwards = $user->awards()->get();
        $userAwardIds = $userAwards->pluck('id')->toArray();

        // Načtení všech odznaků
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
    public function claimBadge(Request $request, Award $award)
    {

        $userAward = UserAward::where('user_id', auth()->id())
            ->where('award_id', $award->id)
            ->first();

        if (!$userAward || !$userAward->earned_at) {
            return redirect()->back()->with('error', 'Tento odznak ještě nemůžeš nárokovat.');
        }

        $userAward->user_description = 'Gratulujeme! Získal jsi odznak ' . $award->name . '!';

        $userAward->claimed_at = now();
        $userAward->save();

        return redirect()->back()->with('success', 'Odznak byl úspěšně nárokován!');
    }
}
