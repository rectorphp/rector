<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony25\Rector\MethodCall\AddViolationToBuildViolationRector;
use Rector\Symfony\Symfony25\Rector\MethodCall\MaxLengthSymfonyFormOptionToAttrRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([AddViolationToBuildViolationRector::class, MaxLengthSymfonyFormOptionToAttrRector::class]);
};
