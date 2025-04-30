<?php
namespace Illuminate\Contracts\Auth;
interface Guard
{
    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    public function user();
}
namespace Illuminate\Support\Facades;
/**
 * @method static \App\Models\User|null user()
 */
class Auth {}