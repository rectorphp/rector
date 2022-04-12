<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Source\SomeTranslator;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\ValueObject\FuncCallToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();

    $services->set(FuncCallToMethodCallRector::class)
        ->configure([
            new FuncCallToMethodCall('view', 'Namespaced\SomeRenderer', 'render'),

            new FuncCallToMethodCall('translate', SomeTranslator::class, 'translateMethod'),

            new FuncCallToMethodCall(
                'Rector\Tests\Transform\Rector\Function_\FuncCallToMethodCallRector\Source\some_view_function',
                'Namespaced\SomeRenderer',
                'render'
            ),
        ]);
};
