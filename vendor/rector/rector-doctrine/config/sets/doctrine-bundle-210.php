<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Bundle210\Rector\Class_\EventSubscriberInterfaceToAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(EventSubscriberInterfaceToAttributeRector::class);
};
