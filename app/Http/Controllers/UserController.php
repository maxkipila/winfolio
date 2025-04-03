<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Minifig;
use App\Http\Resources\_Product;
use App\Http\Resources\_Set;
use App\Http\Resources\_User;
use App\Models\Minifig;
use App\Models\Product;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function get_user(Request $request){

        return _User::init($request->user()->load('products'));
    }

    public function add_product_to_user(Request $request, Product $product){
        $user = Auth::user();
        
        $user->products()->sync([$product->id]);
        return back();
    }

    public function remove_product_from_user(Request $request, Product $product){
        $user = Auth::user();
        
        $user->products()->detach($product->id);
        return back();
    }

    
}
