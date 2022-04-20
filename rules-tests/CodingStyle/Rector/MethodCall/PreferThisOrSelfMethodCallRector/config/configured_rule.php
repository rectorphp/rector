<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rector\CodingStyle\Enum\PreferenceSelfThis;

use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\Config\RectorConfig;
use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;
use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\SomeAbstractTestCase;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(PreferThisOrSelfMethodCallRector::class, [
            SomeAbstractTestCase::class => PreferenceSelfThis::PREFER_SELF(),
            BeLocalClass::class => PreferenceSelfThis::PREFER_THIS(),
            TestCase::class => PreferenceSelfThis::PREFER_SELF(),
        ]);
};
