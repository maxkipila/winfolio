<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\_Product;
use App\Http\Resources\_Set;
use App\Http\Resources\_Theme;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        /*  $products = Product::orderBy('id', 'asc')->paginate($request->get('paginate', 10));

        return Inertia::render('Admin/Products/Index', [
            'products' => _Product::collection($products)
        ]); */
    }

    public function create()
    {

        return Inertia::render('Admin/Products/Create');
    }

    /*   public function store(Request $request)
    {
        $validated = $request->validate([
            'product_num' => 'required|unique:products,product_num',
            'product_type' => 'required',
            'name'        => 'required',
        ]);

        $product = Product::create($validated);

        return redirect()->route('products.index');
    } */
    public function indexSet(Request $request)
    {
        $setsQuery = Product::where('product_type', 'set')
            ->orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())
            ->with(['theme', 'prices']);

        $sets = fn() => _Product::collection(
            $setsQuery->paginate($request->paginate ?? 10)
        );

        $themes = fn() => _Theme::collection(
            Theme::orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())->get()
        );

        return Inertia::render('Admin/Sets/Index', compact('sets', 'themes'));
    }

    public function indexMinifig(Request $request)
    {
        $minifigsQuery = Product::where('product_type', 'minifig')
            ->orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale());
        $minifigs = fn() => _Product::collection($minifigsQuery->paginate($request->paginate ?? 10));

        $themes = fn() => _Theme::collection(
            Theme::orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())->get()
        );

        return Inertia::render('Admin/Minifigs/Index', compact('minifigs', 'themes'));
    }

    public function showMinifig(Product $product)
    {

        if ($product->product_type !== 'minifig') {
            abort(404);
        }

        $product->load('theme');

        $otherMinifigs = Product::where('product_type', 'minifig')
            ->where('theme_id', $product->theme_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(3)
            ->with('theme')
            ->get();

        return Inertia::render('Admin/Minifigs/Detail', [
            'minifig' => $product,
            'otherMinifigs' => $otherMinifigs,
        ]);
    }

    public function showSet(Product $product)
    {
        if ($product->product_type !== 'set') {
            abort(404);
        }


        $product->load('theme', 'price');

        $otherSets = Product::where('product_type', 'set')
            ->where('theme_id', $product->theme_id)
            ->where('id', '!=', $product->id)
            ->take(3)
            ->with('theme')
            ->get();

        return Inertia::render('Admin/Sets/Detail', [
            'set' => $product,
            'otherSets' => $otherSets,
        ]);
    }
}
