<?php

declare(strict_types=1);

use Tracy\Debugger;

if (! function_exists('rd')) {
    function rd($var)
    {
        array_map([Debugger::class, 'dump'], func_get_args());
        return $var;
    }
}

if (! function_exists('rdd')) {
    function rdd($var): void
    {
        rd($var);
        die;
    }
}
