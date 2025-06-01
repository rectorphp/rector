<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony72\Rector\StmtsAwareInterface\PushRequestToRequestStackConstructorRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([PushRequestToRequestStackConstructorRector::class]);
};
