<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304;

if (!\function_exists('RectorPrefix202304\\dump')) {
    /**
     * Tracy\Debugger::dump() shortcut.
     * @tracySkipLocation
     * @param mixed $var
     * @return mixed
     */
    function dump($var)
    {
        \array_map([Tracy\Debugger::class, 'dump'], \func_get_args());
        return $var;
    }
}
if (!\function_exists('RectorPrefix202304\\dumpe')) {
    /**
     * Tracy\Debugger::dump() & exit shortcut.
     * @tracySkipLocation
     * @param mixed $var
     */
    function dumpe($var) : void
    {
        \array_map([Tracy\Debugger::class, 'dump'], \func_get_args());
        if (!Tracy\Debugger::$productionMode) {
            exit;
        }
    }
}
if (!\function_exists('RectorPrefix202304\\bdump')) {
    /**
     * Tracy\Debugger::barDump() shortcut.
     * @tracySkipLocation
     * @param mixed $var
     * @return mixed
     */
    function bdump($var)
    {
        Tracy\Debugger::barDump(...\func_get_args());
        return $var;
    }
}
