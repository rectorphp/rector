<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Set\ValueObject\DowngradeSetList;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(\Rector\Set\ValueObject\DowngradeLevelSetList::DOWN_TO_PHP_72);
    $rectorConfig->import(\Rector\Set\ValueObject\DowngradeSetList::PHP_72);
};
