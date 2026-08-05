<?php

declare (strict_types=1);
namespace RectorPrefix202608;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony25\Rector\MethodCall\AddViolationToBuildViolationRector;
use Rector\Symfony\Symfony25\Rector\MethodCall\MaxLengthSymfonyFormOptionToAttrRector;
use Rector\Symfony\Symfony30\Rector\ClassMethod\GetRequestRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddViolationToBuildViolationRector::class, MaxLengthSymfonyFormOptionToAttrRector::class, GetRequestRector::class]);
};
