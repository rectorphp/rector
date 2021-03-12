<?php

use Rector\PHPUnit\Rector\Class_\ArrayArgumentToDataProviderRector;
use Rector\PHPUnit\ValueObject\ArrayArgumentToDataProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayArgumentToDataProviderRector::class)
        ->call('configure', [[
            ArrayArgumentToDataProviderRector::ARRAY_ARGUMENTS_TO_DATA_PROVIDERS => ValueObjectInliner::inline([
                new ArrayArgumentToDataProvider(
                    'PHPUnit\Framework\TestCase',
                    'doTestMultiple',
                    'doTestSingle',
                    'variable'
                ),
            ]),
        ]]);
};
