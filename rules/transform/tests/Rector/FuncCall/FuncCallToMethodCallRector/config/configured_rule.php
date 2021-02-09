<?php

use Rector\Transform\Rector\FuncCall\FuncCallToMethodCallRector;
use Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\SomeTranslator;
use Rector\Transform\ValueObject\FuncCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FuncCallToMethodCallRector::class)
        ->call('configure', [[
            FuncCallToMethodCallRector::FUNC_CALL_TO_CLASS_METHOD_CALL => ValueObjectInliner::inline([
                new FuncCallToMethodCall('view', 'Namespaced\SomeRenderer', 'render'),
                new FuncCallToMethodCall('translate', SomeTranslator::class, 'translateMethod'),
                new FuncCallToMethodCall(
                    'Rector\Generic\Tests\Rector\Function_\FuncCallToMethodCallRector\Source\some_view_function',
                    'Namespaced\SomeRenderer',
                    'render'
                ),
            ]),
        ]]);
};
