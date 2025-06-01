<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Bundle210\Rector\Class_\EventSubscriberInterfaceToAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://github.com/doctrine/DoctrineBundle/pull/1592
    $rectorConfig->rule(EventSubscriberInterfaceToAttributeRector::class);
};
