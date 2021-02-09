<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::class)->call('configure', [[
        \Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::PROPERTIES_TO_METHOD_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\PropertyFetchToMethodCall(
                \Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Source\Translator::class,
                'locale',
                'getLocale',
                'setLocale'
            ),
            new \Rector\Transform\ValueObject\PropertyFetchToMethodCall(
                'Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Fixture\Fixture2',
                'parameter',
                'getConfig',
                null,
                ['parameter']),
        ]
        ),
    ]]);
};
