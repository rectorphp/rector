<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

final class StaticPHPUnitEnvironment
{
    /**
     * Never ever used static methods if possible, this is just handy for tests + src to prevent duplication.
     */
    public static function isPHPUnitRun() : bool
    {
        return \defined('RectorPrefix20220607\\PHPUNIT_COMPOSER_INSTALL') || \defined('RectorPrefix20220607\\__PHPUNIT_PHAR__');
    }
}
