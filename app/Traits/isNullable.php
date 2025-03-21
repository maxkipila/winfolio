<?php

namespace App\Traits;

trait isNullable
{
    public static function init($resource)
    {
        if (!$resource)
            return NULL;

        return new self($resource);
    }
}
