<?php

declare (strict_types=1);
namespace RectorPrefix20220608;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_50);
};
