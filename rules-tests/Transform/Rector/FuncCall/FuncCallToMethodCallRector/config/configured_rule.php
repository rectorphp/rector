<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Source\SomeTranslator;
use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

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
