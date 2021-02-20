<?php

use Rector\Generic\ValueObject\WrapReturn;
use Rector\Transform\Rector\ClassMethod\WrapReturnRector;
use Rector\Transform\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(WrapReturnRector::class)
        ->call('configure', [[
            WrapReturnRector::TYPE_METHOD_WRAPS => ValueObjectInliner::inline([

                new WrapReturn(SomeReturnClass::class, 'getItem', true),

            ]),
        ]]);
};
