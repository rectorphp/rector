<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit120\Rector\Class_\RemoveOverrideFinalConstructTestCaseRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([PHPUnitSetList::PHPUNIT_MOCK_TO_STUB, PHPUnitSetList::COMPOSER_BASED]);
    $rectorConfig->rules([RemoveOverrideFinalConstructTestCaseRector::class]);
};
