<?php

use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Source\Translator;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PropertyFetchToMethodCallRector::class)->call('configure', [[
        PropertyFetchToMethodCallRector::PROPERTIES_TO_METHOD_CALLS => ValueObjectInliner::inline([

            new PropertyFetchToMethodCall(Translator::class, 'locale', 'getLocale', 'setLocale'),
            new PropertyFetchToMethodCall(
                'Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Fixture\Fixture2',
                'parameter',
                'getConfig',
                null,
                ['parameter']),
        ]
        ),
    ]]);
};
