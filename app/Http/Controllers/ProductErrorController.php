<?php

namespace App\Http\Controllers;

use App\Http\Resources\_ProductError;
use App\Models\ProductError;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductErrorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $errors = _ProductError::collection(
            ProductError::when(
                $request->codes ?? false,
                fn($q) =>
                $q->whereIn(
                    'code',
                    collect($request->codes)
                        ->filter(fn($c) => $c === true)
                        ->map(fn($c, $k) => $k)
                )
            )
                ->when($request->product_id ?? false, fn($q) => $q->where('product_id', $request->product_id))
                ->paginate($request->paginate ?? 10)
        );
        return Inertia::render('Admin/ProductErrors/index', compact('errors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductError $productError)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductError $productError)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductError $productError)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductError $productError)
    {
        //
    }
}
