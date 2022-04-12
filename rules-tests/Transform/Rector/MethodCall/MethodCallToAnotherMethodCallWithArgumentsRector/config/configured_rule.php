<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\Source\NetteServiceDefinition;
use Rector\Transform\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Transform\ValueObject\MethodCallToAnotherMethodCallWithArguments;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->set(MethodCallToAnotherMethodCallWithArgumentsRector::class)
        ->configure([
            new MethodCallToAnotherMethodCallWithArguments(
                NetteServiceDefinition::class,
                'setInject',
                'addTag',
                ['inject']
            ),
        ]);
};
