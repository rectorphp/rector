<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\PackageBuilder\Console\Input;

use RectorPrefix20210510\Symfony\Component\Console\Input\ArgvInput;
final class StaticInputDetector
{
    public static function isDebug() : bool
    {
        $argvInput = new ArgvInput();
        return $argvInput->hasParameterOption(['--debug', '-v', '-vv', '-vvv']);
    }
}
