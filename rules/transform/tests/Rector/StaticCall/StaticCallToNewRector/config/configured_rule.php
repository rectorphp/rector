<?php

use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse;
use Rector\Transform\ValueObject\StaticCallToNew;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(StaticCallToNewRector::class)
        ->call('configure', [[
            StaticCallToNewRector::STATIC_CALLS_TO_NEWS => ValueObjectInliner::inline([
                new StaticCallToNew(SomeJsonResponse::class, 'create'),
            ]),
        ]]);
};
