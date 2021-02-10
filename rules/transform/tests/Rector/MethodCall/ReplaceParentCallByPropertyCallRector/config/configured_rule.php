<?php

use Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Transform\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReplaceParentCallByPropertyCallRector::class)->call(
        'configure',
        [[
            ReplaceParentCallByPropertyCallRector::PARENT_CALLS_TO_PROPERTIES => ValueObjectInliner::inline([

                new ReplaceParentCallByPropertyCall(
                    TypeClassToReplaceMethodCallBy::class,
                    'someMethod',
                    'someProperty'
                ),

            ]),
        ]]
    );
};
