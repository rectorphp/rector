<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\PHPUnit\Set\PHPUnitLevelSetList;
use RectorPrefix20220606\Rector\PHPUnit\Set\PHPUnitSetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_91);
    $rectorConfig->import(PHPUnitLevelSetList::UP_TO_PHPUNIT_90);
};
