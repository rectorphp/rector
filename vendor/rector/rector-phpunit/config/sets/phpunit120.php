<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit110\Rector\CallLike\AssertContainsOnlyMethodCallRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\AssertIsTypeMethodCallRector;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\RemoveOverrideFinalConstructTestCaseRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([PHPUnitSetList::PHPUNIT_MOCK_TO_STUB]);
    $rectorConfig->rules([
        RemoveOverrideFinalConstructTestCaseRector::class,
        // deprecated in PHPUnit 11.5, repeated here for a direct 11.4 → 12.0 upgrade
        AssertContainsOnlyMethodCallRector::class,
        AssertIsTypeMethodCallRector::class,
    ]);
};
