<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Source\Generator;
use Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Source\Translator;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(PropertyFetchToMethodCallRector::class, [

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
