<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_Award;
use App\Http\Resources\_AwardCondition;
use App\Models\Award;
use App\Models\AwardCondition;
use App\Models\Category;
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
        $award->load('conditions.product', 'conditions.category');
        $awards = _Award::init($award);
        return Inertia::render('Admin/Awards/Credit', [
            'awards' => $awards,
        ]);
    }
    public function store(Request $request)
    {
        /*  dd($request->all()); */

        if ($request->has('category_id') && is_array($request->input('category_id'))) {
            $catArray = $request->input('category_id');
            $firstCatId = $catArray[0]['id'] ?? null;
            if ($firstCatId) {
                $request->merge([
                    'category_id' => $firstCatId,
                ]);
            } else {

                $request->merge([
                    'category_id' => null,
                ]);
            }
        }

        $awardData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
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

        if (!empty($conditionData['condition_type'])) {
            $award->conditions()->create($conditionData);
        }

        return redirect()
            ->route('admin.awards.index')
            ->with('success', 'Ocenění bylo úspěšně vytvořeno.');
    }
    public function removeField(Request $request, Award $award, AwardCondition $condition, $field)
    {
        if (!in_array($field, ['category', 'product'])) {
            abort(400, 'Neplatný parametr');
        }

        if ($field === 'category') {
            $condition->update(['category_id' => null]);
            $message = 'Kategorie byla úspěšně odstraněna.';
        } else {
            $condition->update(['product_id' => null]);
            $message = 'Hodnota produktu byla úspěšně odstraněna.';
        }
        return redirect()
            ->route('admin.awards.edit', $award)
            ->with('success', $message);
    }


    public function update(Request $request, Award $award)
    {
        // Zpracování pole pro product_name: pokud přichází jako pole, z merge se uloží první položka do product_id
        if ($request->has('product_name') && is_array($request->input('product_name'))) {
            $firstItem = $request->input('product_name')[0] ?? null;
            if ($firstItem && isset($firstItem['id'])) {
                $request->merge([
                    'product_id' => $firstItem['id'],
                ]);
            }
        }

        // Zpracování pole pro category_id: pokud přichází jako pole, vezme se první položka
        if ($request->has('category_id') && is_array($request->input('category_id'))) {
            $catArray = $request->input('category_id');
            $firstCatId = $catArray[0]['id'] ?? null;
            if ($firstCatId) {
                $request->merge([
                    'category_id' => $firstCatId,
                ]);
            } else {
                $request->merge([
                    'category_id' => null,
                ]);
            }
        }

        $awardData = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $conditionData = $request->validate([
            'condition_type'     => 'required|string',
            'product_id'         => 'nullable|integer',
            'category_id'        => 'nullable',
            'required_count'     => 'nullable|integer',
            'required_value'     => 'nullable|numeric',
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

        return redirect()->route('admin.awards.index')->with('success', 'News deleted successfully.');
    }
}
