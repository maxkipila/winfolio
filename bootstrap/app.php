<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\RedirectIfInertia;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,

        ]);

        $middleware->alias([
            'force' => RedirectIfInertia::class,

        ]);
        $middleware->redirectUsersTo(function (Request $request) {
            /*  dd(Auth::guard('admins')->user(), Auth::getDefaultDriver()); */
            $guard = Auth::guard('admins')->check() ? 'admins' : 'web';
            return Auth::guard($guard)->user() instanceof \App\Models\Admin ? RouteServiceProvider::ADMIN_HOME : RouteServiceProvider::HOME;
        });

        $middleware->redirectGuestsTo(function (Request $request) {
            return Route::currentRouteNamed('admin.*') ? RouteServiceProvider::ADMIN_LOGIN : RouteServiceProvider::LOGIN;
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
