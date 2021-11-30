<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClass;
use Rector\Tests\Transform\Rector\New_\NewToMethodCallRector\Source\MyClassFactory;
use Rector\Transform\Rector\New_\NewToMethodCallRector;
use Rector\Transform\ValueObject\NewToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(NewToMethodCallRector::class)
        ->configure([new NewToMethodCall(MyClass::class, MyClassFactory::class, 'create')]);
};
