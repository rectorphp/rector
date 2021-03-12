<?php

use Rector\Transform\Rector\ClassConstFetch\ClassConstFetchToValueRector;
use Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToValueRector\Source\OldClassWithConstants;
use Rector\Transform\ValueObject\ClassConstFetchToValue;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ClassConstFetchToValueRector::class)
        ->call('configure', [[
            ClassConstFetchToValueRector::CLASS_CONST_FETCHES_TO_VALUES => ValueObjectInliner::inline([
                new ClassConstFetchToValue(OldClassWithConstants::class, 'DEVELOPMENT', 'development'),
                new ClassConstFetchToValue(OldClassWithConstants::class, 'PRODUCTION', 'production'),
            ]),
        ]]);
};
