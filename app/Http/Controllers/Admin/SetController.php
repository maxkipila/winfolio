<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_Set;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class SetController extends Controller
{


    public function index(Request $request)
    {
        $sets = fn() => _Set::collection(Set::orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())->paginate($request->paginate ?? 10));
        return Inertia::render('Admin/Sets/Index', compact('sets'));
    }
}
