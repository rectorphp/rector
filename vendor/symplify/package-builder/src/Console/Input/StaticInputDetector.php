<?php

declare (strict_types=1);
namespace RectorPrefix20211111\Symplify\PackageBuilder\Console\Input;

use RectorPrefix20211111\Symfony\Component\Console\Input\ArgvInput;
/**
 * @api
 */
final class StaticInputDetector
{
    public static function isDebug() : bool
    {
        $argvInput = new \RectorPrefix20211111\Symfony\Component\Console\Input\ArgvInput();
        return $argvInput->hasParameterOption(['--debug', '-v', '-vv', '-vvv']);
    }
}
