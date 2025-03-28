<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_Set;
use App\Http\Resources\_Theme;
use App\Models\Product;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class SetController extends Controller
{
    public function index(Request $request)
    {
        $setsQuery = Product::where('product_type', 'set')
            ->orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale());
        $sets = fn() => _Set::collection(
            $setsQuery->with('prices')->paginate($request->paginate ?? 10)
        )->additional(['whenLoaded' => true]);
        $themes = fn() => _Theme::collection(
            Theme::orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())->get()
        );

        return Inertia::render('Admin/Sets/Index', compact('sets', 'themes', 'prices'));
    }

    public function show(Product $product)
    {

        $legoSet = new _Set($product);

        return Inertia::render('Admin/Sets/Detail', [
            'set' => $legoSet,
        ]);
    }
}
