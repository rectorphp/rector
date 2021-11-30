<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Source\Generator;
use Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Source\Translator;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PropertyFetchToMethodCallRector::class)
        ->configure([

            new PropertyFetchToMethodCall(Translator::class, 'locale', 'getLocale', 'setLocale'),
            new PropertyFetchToMethodCall(Generator::class, 'word', 'word'),
            new PropertyFetchToMethodCall(
                'Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Fixture\Fixture2',
                'parameter',
                'getConfig',
                null,
                ['parameter']
            ),
        ]);
};
