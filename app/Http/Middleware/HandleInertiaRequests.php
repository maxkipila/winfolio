<?php

namespace App\Http\Middleware;

use App\Http\Resources\_Admin;
use App\Http\Resources\_Minifig;
use App\Http\Resources\_Product;
use App\Http\Resources\_Set;
use App\Http\Resources\_Theme;
use App\Http\Resources\_User;
use App\Models\Minifig;
use App\Models\Product;
use App\Models\Set;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
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
        /*         $minifigs = fn(): AnonymousResourceCollection => _Minifig::collection(Minifig::search(['id', 'fig_num', 'name'], $request->q ?? '', 6, App::getLocale())->get());
        $sets = fn(): AnonymousResourceCollection => _Set::collection(Set::search(['id', 'set_num', 'name'], $request->q ?? '', 6, App::getLocale())->get());
 */
        $themes = fn(): AnonymousResourceCollection => _Theme::collection(Theme::search(['id', 'name'], $request->q ?? '', 6, App::getLocale())->get());
        /* $admins = fn(): AnonymousResourceCollection => _Admin::collection(User::search(['first_name', 'last_name', DB::raw("(CONCAT(first_name,' ', last_name))"), 'email', 'id'], $request->q ?? '', 6, App::getLocale())->get());
        $activities = fn(): AnonymousResourceCollection => _Activity::collection(Activity::search(['id', 'name'], $request->q ?? '', 6, App::getLocale())->get());
    */
        $products = fn(): AnonymousResourceCollection => _Product::collection(
            Product::search(['id', 'name', 'product_num'], $request->q ?? '', 6, App::getLocale())->get()
        );
        $searchProducts = fn(): AnonymousResourceCollection => _Product::collection(
            Product::search(['id', 'name', 'product_num'], $request->q ?? '', 6, App::getLocale())->get()
        );


        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn() => (
                    Gate::allows('admin')
                    ? ($is_admin_section ? _Admin::make($request->user()) : null)
                    : _User::make($request->user()?->load('products'))
                ),
            ],
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            "search_all" => Inertia::lazy(
                fn() => collect()
                    ->merge($users())
                    ->merge($products())
                    ->merge($themes())
                /*  ->merge($minifigs())
                    ->merge($sets()) */
                /* ->merge($admins()) */
                /* ->merge($activities()) */
            ),
            'searchProducts' => Inertia::lazy($products),
            'flash' => Session::get('flash'),
            /*  'searchAllUsers' => Inertia::lazy($searchAllUser),
            'searchAllSets' => Inertia::lazy($this->searchByModel(Set::class, 'name', _Set::class, $request->q ?? "")),
            'searchAllMinifigs' => Inertia::lazy($this->searchByModel(Minifig::class, 'name', _Minifig::class, $request->q ?? "")), */

        ];
    }
}
