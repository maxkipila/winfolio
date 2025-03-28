<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_News;
use App\Http\Resources\_User;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(Request $request)
    {

        $news = fn() => _News::collection(
            News::orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())
                ->paginate($request->paginate ?? 10)
        );
        return Inertia::render('Admin/News/Index', compact('news'));
    }
}
