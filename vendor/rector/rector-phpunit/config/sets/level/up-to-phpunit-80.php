<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitLevelSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_80);
    $rectorConfig->import(PHPUnitLevelSetList::UP_TO_PHPUNIT_70);
};
