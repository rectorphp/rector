<?php

declare (strict_types=1);
namespace RectorPrefix202311;

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([AddOverrideAttributeToOverriddenMethodsRector::class]);
};
