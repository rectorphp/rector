<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector\Fixture;

class SkipIndirectStringNumber
{
    private const MIN_PHP_VERSION = '7.3';

    public function run()
    {
        version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<');
        version_compare(self::MIN_PHP_VERSION, PHP_VERSION, '<');
    }
}

?>
