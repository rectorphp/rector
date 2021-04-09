<?php

declare(strict_types=1);

use Rector\Arguments\Rector\MethodCall\ValueObjectWrapArgRector;
use Rector\Arguments\ValueObject\ValueObjectWrapArg;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ValueObjectWrapArgRector::class)
        ->call('configure', [[
            ValueObjectWrapArgRector::VALUE_OBJECT_WRAP_ARGS => ValueObjectInliner::inline([
                new ValueObjectWrapArg(
                    'Rector\Tests\Arguments\Rector\MethodCall\ValueObjectWrapArgRector\Fixture\SomeClass',
                    'something',
                    0,
                    'Number'
                ),
                new ValueObjectWrapArg(
                    'Rector\Tests\Arguments\Rector\MethodCall\ValueObjectWrapArgRector\Fixture\SkipAlreadyNew',
                    'something',
                    0,
                    'Number'
                ),
            ]),
        ]]);
};
