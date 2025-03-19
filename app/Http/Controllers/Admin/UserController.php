<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\_Subscription;
use App\Http\Resources\_User;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $users = fn() => _User::collection(
            User::with('subscriptions')
                ->orderByRelation($request->sort ?? [], ['id', 'asc'], App::getLocale())
                ->paginate($request->paginate ?? 10)
        );

        /*    $subscriptions = fn() => _Subscription::collection(
            Subscription::orderBy('id', 'desc')
                ->paginate($request->paginate ?? 10)
        ); */

        return Inertia::render('Admin/Users/Index', compact('users', /* 'subscriptions' */));
    }

    public function show(Request $request, User $user)
    {
        $user = _User::init($user);

        return Inertia::render('Admin/Users/Detail', [
            'user' => $user
        ]);
    }





    /*    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    public function edit($id)
    {
        return Inertia::render('Admin/Users/Edit', [
            'id' => $id,
        ]);
    } */
}
