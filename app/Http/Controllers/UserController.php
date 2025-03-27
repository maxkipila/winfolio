<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Minifig;
use App\Http\Resources\_Product;
use App\Http\Resources\_Set;
use App\Models\Minifig;
use App\Models\Product;
use App\Models\Set;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function dashboard(Request $request){
        
        $products = _Product::collection(Product::latest()->paginate($request->paginate ?? 10));
        return Inertia::render('Dashboard', compact('products'));
    }

    public function profile(Request $request){

        return Inertia::render('Profile/index');
    }
}
