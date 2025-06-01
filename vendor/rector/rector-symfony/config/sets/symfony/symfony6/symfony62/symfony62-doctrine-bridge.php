<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony62\Rector\ClassMethod\ParamConverterAttributeToMapEntityAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ParamConverterAttributeToMapEntityAttributeRector::class);
};
