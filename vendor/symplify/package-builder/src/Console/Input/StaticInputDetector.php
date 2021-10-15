<?php

declare (strict_types=1);
namespace RectorPrefix20211015\Symplify\PackageBuilder\Console\Input;

use RectorPrefix20211015\Symfony\Component\Console\Input\ArgvInput;
/**
 * @api
 */
final class StaticInputDetector
{
    public static function isDebug() : bool
    {
        $argvInput = new \RectorPrefix20211015\Symfony\Component\Console\Input\ArgvInput();
        return $argvInput->hasParameterOption(['--debug', '-v', '-vv', '-vvv']);
    }
}
