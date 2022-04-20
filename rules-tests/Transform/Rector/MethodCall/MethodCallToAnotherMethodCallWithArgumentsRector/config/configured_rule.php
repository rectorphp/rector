<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\Source\NetteServiceDefinition;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(MethodCallToAnotherMethodCallWithArgumentsRector::class, [
            new MethodCallToAnotherMethodCallWithArguments(
                NetteServiceDefinition::class,
                'setInject',
                'addTag',
                ['inject']
            ),
        ]);
};
