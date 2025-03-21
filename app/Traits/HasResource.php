<?php

namespace App\Traits;

trait HasResource
{
    /**
     * Get resource to use for morphing
     *
     * @return string
     */
    public function resource()
    {
        return "App\\Http\\Resources\\_" . str_replace("App\\Models\\", "", static::class);
    }
}
