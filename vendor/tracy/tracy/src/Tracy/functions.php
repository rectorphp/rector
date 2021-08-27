<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210827;

if (!\function_exists('RectorPrefix20210827\\dump')) {
    /**
     * Tracy\Debugger::dump() shortcut.
     * @tracySkipLocation
     */
    function dump($var)
    {
        \array_map([\RectorPrefix20210827\Tracy\Debugger::class, 'dump'], \func_get_args());
        return $var;
    }
}
if (!\function_exists('RectorPrefix20210827\\dumpe')) {
    /**
     * Tracy\Debugger::dump() & exit shortcut.
     * @tracySkipLocation
     */
    function dumpe($var) : void
    {
        \array_map([\RectorPrefix20210827\Tracy\Debugger::class, 'dump'], \func_get_args());
        if (!\RectorPrefix20210827\Tracy\Debugger::$productionMode) {
            exit;
        }
    }
}
if (!\function_exists('RectorPrefix20210827\\bdump')) {
    /**
     * Tracy\Debugger::barDump() shortcut.
     * @tracySkipLocation
     */
    function bdump($var)
    {
        \RectorPrefix20210827\Tracy\Debugger::barDump(...\func_get_args());
        return $var;
    }
}
