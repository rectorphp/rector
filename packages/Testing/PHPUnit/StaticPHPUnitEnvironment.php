<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Testing\PHPUnit;

final class StaticPHPUnitEnvironment
{
    /**
     * Never ever used static methods if possible, this is just handy for tests + src to prevent duplication.
     */
    public static function isPHPUnitRun() : bool
    {
        return \defined('PHPUNIT_COMPOSER_INSTALL') || \defined('__PHPUNIT_PHAR__');
    }
}
