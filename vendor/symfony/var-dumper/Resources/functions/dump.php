<?php

namespace RectorPrefix20210718;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use RectorPrefix20210718\Symfony\Component\VarDumper\VarDumper;
if (!\function_exists('RectorPrefix20210718\\dump')) {
    /**
     * @author Nicolas Grekas <p@tchwork.com>
     */
    function dump($var, ...$moreVars)
    {
        \RectorPrefix20210718\Symfony\Component\VarDumper\VarDumper::dump($var);
        foreach ($moreVars as $v) {
            \RectorPrefix20210718\Symfony\Component\VarDumper\VarDumper::dump($v);
        }
        if (1 < \func_num_args()) {
            return \func_get_args();
        }
        return $var;
    }
}
if (!\function_exists('RectorPrefix20210718\\dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $v) {
            \RectorPrefix20210718\Symfony\Component\VarDumper\VarDumper::dump($v);
        }
        exit(1);
    }
}
