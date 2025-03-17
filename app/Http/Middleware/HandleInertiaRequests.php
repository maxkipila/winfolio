<?php

namespace App\Http\Middleware;

use App\Http\Resources\_Admin;
use App\Http\Resources\_User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $is_admin_section = Str::startsWith(Route::currentRouteName(), 'admin.');


        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn() => (
                    Gate::allows('admin')
                    ? ($is_admin_section ? _Admin::make($request->user()) : null)
                    : _User::make($request->user())
                ),
            ],
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
