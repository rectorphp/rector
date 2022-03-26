<?php

declare (strict_types=1);
namespace RectorPrefix20220326\Symplify\PackageBuilder\Console\Input;

use RectorPrefix20220326\Symfony\Component\Console\Input\ArgvInput;
/**
 * @api
 */
final class StaticInputDetector
{
    public static function isDebug() : bool
    {
        $argvInput = new \RectorPrefix20220326\Symfony\Component\Console\Input\ArgvInput();
        return $argvInput->hasParameterOption(['--debug', '-v', '-vv', '-vvv']);
    }
}
