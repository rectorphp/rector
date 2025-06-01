<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony63\Rector\Class_\SignalableCommandInterfaceReturnTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/symfony/symfony/commit/1650e3861b5fcd931e5d3eb1dd84bad764020d8e
        SignalableCommandInterfaceReturnTypeRector::class,
    ]);
};
