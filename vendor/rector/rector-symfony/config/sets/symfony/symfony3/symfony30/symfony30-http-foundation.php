<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony30\Rector\ClassMethod\GetRequestRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([GetRequestRector::class]);
};
