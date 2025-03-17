<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_Minifig;
use App\Models\Minifig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class MinifigController extends Controller
{
    public function index(Request $request)
    {
        $minifigs = fn() => _Minifig::collection(Minifig::orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())->paginate($request->paginate ?? 10));
        return Inertia::render('Admin/Minifigs/Index', compact('minifigs'));
    }
}
