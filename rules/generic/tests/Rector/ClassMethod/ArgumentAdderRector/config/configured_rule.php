<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\ClassMethod\ArgumentAdderRector::class)->call('configure', [[
        \Rector\Generic\Rector\ClassMethod\ArgumentAdderRector::ADDED_ARGUMENTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            // covers https://github.com/rectorphp/rector/issues/4267
            new \Rector\Generic\ValueObject\ArgumentAdder(
                \Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder::class,
                'sendResetLinkResponse',
                0,
                'request',
                null,
                'Illuminate\Http\Illuminate\Http'
            ),
            new \Rector\Generic\ValueObject\ArgumentAdder(
                \Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder::class,
                'compile',
                0,
                'isCompiled',
                false
            ),
            new \Rector\Generic\ValueObject\ArgumentAdder(
                \Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder::class,
                'addCompilerPass',
                2,
                'priority',
                0,
                'int'
            ),
            // scoped
            new \Rector\Generic\ValueObject\ArgumentAdder(
                \Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeParentClient::class,
                'submit',
                2,
                'serverParameters',
                [],
                'array',
                \Rector\Generic\NodeAnalyzer\ArgumentAddingScope::SCOPE_PARENT_CALL
            ),
            new \Rector\Generic\ValueObject\ArgumentAdder(
                \Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\Source\SomeParentClient::class,
                'submit',
                2,
                'serverParameters',
                [],
                'array',
                \Rector\Generic\NodeAnalyzer\ArgumentAddingScope::SCOPE_CLASS_METHOD
            ),
        ]),
    ]]);
};
