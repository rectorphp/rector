<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector::class)->call(
        'configure',
        [[
            \Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector::PARENT_CALLS_TO_PROPERTIES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
                
























                new \Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall(
                    \Rector\Transform\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy::class,
                    'someMethod',
                    'someProperty'
                ),
























                
            ]),
        ]]
    );
};
