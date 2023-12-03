<?php

declare (strict_types=1);
namespace RectorPrefix202312;

use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([AddOverrideAttributeToOverriddenMethodsRector::class, AddTypeToConstRector::class]);
};
