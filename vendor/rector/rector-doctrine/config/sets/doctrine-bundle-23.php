<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Bundle230\Rector\Class_\AddAnnotationToRepositoryRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(AddAnnotationToRepositoryRector::class);
};
