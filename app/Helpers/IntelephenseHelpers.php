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
interface Factory
{
    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    public function user();
    
    public function check();
}

namespace Illuminate\Support\Facades;
/**
 * @method static \App\Models\User|null user()
 */
class Auth {}