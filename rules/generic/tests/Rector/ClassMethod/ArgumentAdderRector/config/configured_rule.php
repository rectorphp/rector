<?php

use Rector\Generic\NodeAnalyzer\ArgumentAddingScope;
use Rector\Generic\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;
use Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeParentClient;
use Rector\Generic\ValueObject\ArgumentAdder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ArgumentAdderRector::class)->call('configure', [[
        ArgumentAdderRector::ADDED_ARGUMENTS => ValueObjectInliner::inline([
            // covers https://github.com/rectorphp/rector/issues/4267
            new ArgumentAdder(
                SomeContainerBuilder::class,
                'sendResetLinkResponse',
                0,
                'request',
                null,
                'Illuminate\Http\Illuminate\Http'
            ),
            new ArgumentAdder(SomeContainerBuilder::class, 'compile', 0, 'isCompiled', false),
            new ArgumentAdder(SomeContainerBuilder::class, 'addCompilerPass', 2, 'priority', 0, 'int'),
            // scoped
            new ArgumentAdder(
                SomeParentClient::class,
                'submit',
                2,
                'serverParameters',
                [],
                'array',
                ArgumentAddingScope::SCOPE_PARENT_CALL
            ),
            new ArgumentAdder(
                SomeParentClient::class,
                'submit',
                2,
                'serverParameters',
                [],
                'array',
                ArgumentAddingScope::SCOPE_CLASS_METHOD
            ),
        ]),
    ]]);
};
