<?php

namespace App\Traits;

trait HasUtils
{
    function sign($n)
    {
        return ($n > 0) - ($n < 0);
    }

    function floor($n)
    {
        return floor($n * 100) / 100;
    }
}
