<?php

declare(strict_types=1);

if (! function_exists('rd')) {
    function rd($var)
    {
        array_map([Tracy\Debugger::class, 'dump'], func_get_args());
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
