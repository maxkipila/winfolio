<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Users/Index');
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
