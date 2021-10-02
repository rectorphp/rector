<?php

declare(strict_types=1);

use Rector\Generics\Rector\ClassMethod\GenericClassMethodParamRector;
use Rector\Generics\ValueObject\GenericClassMethodParam;
use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\Contract\GenericSearchInterface;
use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\SomeMapperInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(GenericClassMethodParamRector::class)
        ->call('configure', [[
            GenericClassMethodParamRector::GENERIC_CLASS_METHOD_PARAMS => ValueObjectInliner::inline([
                new GenericClassMethodParam(
                    SomeMapperInterface::class,
                    'getParams',
                    0,
                    GenericSearchInterface::class
                ),
            ]),
        ]]);
};
