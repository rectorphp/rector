<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService;
use Rector\Tests\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;
use Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Transform\ValueObject\ServiceGetterToConstructorInjection;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(ServiceGetterToConstructorInjectionRector::class)
        ->configure([
            new ServiceGetterToConstructorInjection(FirstService::class, 'getAnotherService', AnotherService::class),
        ]);
};
