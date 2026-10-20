<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit;

final class StaticPHPUnitEnvironment
{
    // the constant is defined once at phpunit bootstrap and never changes within a process,
    // so the lookup is memoized - isPHPUnitRun() is called hundreds of thousands of times per run
    private static ?bool $isPHPUnitRun = null;
    /**
     * Never ever used static methods if possible, this is just handy for tests + src to prevent duplication.
     */
    public static function isPHPUnitRun(): bool
    {
        return self::$isPHPUnitRun ??= defined('PHPUNIT_COMPOSER_INSTALL');
    }
}
