<?php

namespace App\Http\Middleware;

use App\Http\Resources\_Admin;
use App\Http\Resources\_Minifig;
use App\Http\Resources\_Set;
use App\Http\Resources\_User;
use App\Models\Minifig;
use App\Models\Set;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
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
        $users = fn(): AnonymousResourceCollection => _User::collection(User::search(['first_name', 'last_name', DB::raw("(CONCAT(first_name,' ', last_name))"), 'email', 'id'], $request->q ?? '', 6, App::getLocale())->get());
        $minifigs = fn(): AnonymousResourceCollection => _Minifig::collection(Minifig::search(['id', 'fig_num', 'name'], $request->q ?? '', 6, App::getLocale())->get());
        $sets = fn(): AnonymousResourceCollection => _Set::collection(Set::search(['id', 'set_num', 'name'], $request->q ?? '', 6, App::getLocale())->get());
        $themes = fn(): AnonymousResourceCollection => _Minifig::collection(Minifig::search(['id', 'name'], $request->q ?? '', 6, App::getLocale())->get());



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
            "search_all" => Inertia::lazy(
                fn() => collect()
                    ->merge($users())
                    ->merge($minifigs())
                    ->merge($sets())
                    ->merge($themes())
                /* ->merge($admins()) */
                /* ->merge($activities()) */
            ),
            /*  'searchAllUsers' => Inertia::lazy($searchAllUser),
            'searchAllSets' => Inertia::lazy($this->searchByModel(Set::class, 'name', _Set::class, $request->q ?? "")),
            'searchAllMinifigs' => Inertia::lazy($this->searchByModel(Minifig::class, 'name', _Minifig::class, $request->q ?? "")), */

        ];
    }
}
