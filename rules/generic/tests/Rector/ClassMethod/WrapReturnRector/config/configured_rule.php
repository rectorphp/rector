<?php

use Rector\Generic\Rector\ClassMethod\WrapReturnRector;
use Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;
use Rector\Generic\ValueObject\WrapReturn;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(WrapReturnRector::class)->call('configure', [[
        WrapReturnRector::TYPE_METHOD_WRAPS => ValueObjectInliner::inline([

            new WrapReturn(SomeReturnClass::class, 'getItem', true),

        ]),
    ]]);
};
