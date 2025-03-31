<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function exists(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users'
        ]);

        // dd($request);

        return back();
    }
    public function adminExists(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:admins'
        ]);

        // dd($request);

        return back();
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|exists:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name ?? NULL,
            'last_name' => $request->last_name ?? NULL,
            'nickname' => $request->nickname ?? NULL,
            'prefix' => $request->prefix ?? NULL,
            'phone' => $request->phone ?? NULL,
            'day' => $request->day ?? NULL,
            'month' => $request->month ?? NULL,
            'year' => $request->year ?? NULL,
            'street' => $request->street ?? NULL,
            'street_2' => $request->street_2 ?? NULL,

            'psc' => $request->psc ?? NULL,
            'city' => $request->city ?? NULL,
            'country' => $request->country ?? NULL,
        ]);


        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    public function preregister(Request $request)
    {
        $request->validate([
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
        ]);

        $user = User::create([
            'email' => $request->email,
        ]);

        $user->generateTwoFactorCode();

        back();
    }

    public function confirmEmail(Request $request)
    {
        $request->validate([
            'code' => ['required'],
            'email' => 'required|exists:users'
        ]);

        $user = User::where('email', $request->email)->first();

        // dd($request->all(), $user);

        /* if ($request->code != $user->two_fa_code && $user->two_fa_expires_at->isAfter(now()))
            throw ValidationException::withMessages(['code' => __('auth.failed')]); */

        // || $user->two_fa_expires_at->isBefore(now())
        if (!Hash::check($request->code, $user->two_fa_code)) {
            throw ValidationException::withMessages(['code' => __('auth.failed')]);
        }

        $user->markEmailAsVerified();

        return back();
    }
}
