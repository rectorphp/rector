<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\MethodCall\AddViolationToBuildViolationRector;
use Rector\Symfony\Rector\MethodCall\MaxLengthSymfonyFormOptionToAttrRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([AddViolationToBuildViolationRector::class, MaxLengthSymfonyFormOptionToAttrRector::class]);
};
