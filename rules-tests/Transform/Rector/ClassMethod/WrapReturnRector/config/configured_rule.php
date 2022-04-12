<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;
use Rector\Transform\Rector\ClassMethod\WrapReturnRector;
use Rector\Transform\ValueObject\WrapReturn;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(WrapReturnRector::class)
        ->configure([new WrapReturn(SomeReturnClass::class, 'getItem', true)]);
};
