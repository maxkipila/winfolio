<?php

namespace App\Http\Controllers;

use App\Http\Resources\_Minifig;
use App\Http\Resources\_Set;
use App\Models\Minifig;
use App\Models\Set;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function dashboard(Request $request){
        $sets = _Set::collection(Set::latest()->paginate($request->paginate ?? 10));
        $minifigs = _Minifig::collection(Minifig::latest()->paginate($request->paginate ?? 10));
        return Inertia::render('Dashboard', compact('sets', 'minifigs'));
    }

    public function profile(Request $request){

        return Inertia::render('Profile/index');
    }
}
