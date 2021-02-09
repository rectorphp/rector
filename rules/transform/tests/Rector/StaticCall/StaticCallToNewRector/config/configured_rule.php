<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\StaticCall\StaticCallToNewRector::class)->call('configure', [[
        \Rector\Transform\Rector\StaticCall\StaticCallToNewRector::STATIC_CALLS_TO_NEWS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\StaticCallToNew(
                \Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse::class,
                'create'
            ),
























            
        ]),
    ]]);
};
