<?php

declare (strict_types=1);
namespace RectorPrefix20220609\Symplify\EasyTesting\PHPUnit;

/**
 * @api
 */
final class StaticPHPUnitEnvironment
{
    /**
     * Never ever used static methods if not neccesary, this is just handy for tests + src to prevent duplication.
     */
    public static function isPHPUnitRun() : bool
    {
        return \defined('RectorPrefix20220609\\PHPUNIT_COMPOSER_INSTALL') || \defined('RectorPrefix20220609\\__PHPUNIT_PHAR__');
    }
}
