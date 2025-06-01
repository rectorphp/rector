<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Orm28\Rector\MethodCall\IterateToToIterableRector;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://github.com/doctrine/orm/blob/2.8.x/UPGRADE.md#deprecated-doctrineormabstractqueryiterator
    $rectorConfig->rule(IterateToToIterableRector::class);
};
