<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_Minifig;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class MinifigController extends Controller
{
    public function index(Request $request)
    {
        $minifigsQuery = Product::where('product_type', 'minifig')
            ->orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale());

        $minifigs = function () use ($minifigsQuery, $request) {
            return _Minifig::collection($minifigsQuery->paginate($request->paginate ?? 10));
        };

        return Inertia::render('Admin/Minifigs/Index', compact('minifigs'));
    }

    public function show(Product $product)
    {

        $minifig = new _Minifig($product);

        return Inertia::render('Admin/Minifigs/Detail', [
            'minifig' => $minifig
        ]);
    }
}
