<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(\Rector\PHPUnit\Set\PHPUnitSetList::PHPUNIT_50);
};
