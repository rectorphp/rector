<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Set\ValueObject\DowngradeSetList;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([DowngradeLevelSetList::DOWN_TO_PHP_56, DowngradeSetList::PHP_56]);
};
