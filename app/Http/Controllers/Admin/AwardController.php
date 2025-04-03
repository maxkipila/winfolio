<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_Award;
use App\Http\Resources\_AwardCondition;
use App\Models\Award;
use App\Models\User;
use App\Services\AwardCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class AwardController extends Controller
{

    protected AwardCheckerService $awardChecker;

    public function __construct(AwardCheckerService $awardChecker)
    {
        $this->awardChecker = $awardChecker;
    }

    public function checkAwardsForUser(Request $request, User $user)
    {
        $newAwards = $this->awardChecker->checkUserAwards($user);
        return response()->json($newAwards);
    }

    public function index(Request $request)
    {

        $awards = fn() => _Award::collection(
            Award::orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())
                ->with('conditions')
                ->paginate($request->paginate ?? 10)

        );
        return Inertia::render('Admin/Awards/Index', compact('awards'));
    }

    public function create()
    {
        return Inertia::render('Admin/Awards/Credit');
    }

    public function edit(Award $award)
    {

        $awards = _Award::init($award);
        $awards->load('conditions');
        return Inertia::render('Admin/Awards/Credit', [
            'awards' => $awards,
        ]);
    }
    public function store(Request $request)
    {
        $awardData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

        ]);

        $conditionData = $request->validate([
            'condition_type' => 'required|string',
            'product_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'required_count' => 'nullable|integer',
            'required_value' => 'nullable|numeric',
            'required_percentage' => 'nullable|numeric',
        ]);

        $award = Award::create($awardData);

        if ($request->filled('condition_type')) {
            $award->conditions()->create($conditionData);
        }

        return redirect()
            ->route('admin.awards.index')
            ->with('success', 'Ocenění bylo úspěšně vytvořeno.');
    }
    public function update(Request $request, Award $award)
    {
        $awardData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',

        ]);

        $conditionData = $request->validate([
            'condition_type' => 'required|string',
            'product_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'required_count' => 'nullable|integer',
            'required_value' => 'nullable|numeric',
            'required_percentage' => 'nullable|numeric',
        ]);

        $award->update($awardData);

        if ($request->filled('condition_type')) {
            $existingCondition = $award->conditions()->first();
            if ($existingCondition) {
                $existingCondition->update($conditionData);
            } else {
                $award->conditions()->create($conditionData);
            }
        }

        return redirect()
            ->route('admin.awards.index')
            ->with('success', 'Ocenění bylo úspěšně aktualizováno.');
    }

    public function destroy(Award $award)
    {
        $award->delete();
        return response()->noContent();
    }
}
