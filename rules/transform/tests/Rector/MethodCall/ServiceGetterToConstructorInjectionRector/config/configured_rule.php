<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector::class)->call(
        'configure',
        [[
            \Rector\Transform\Rector\MethodCall\ServiceGetterToConstructorInjectionRector::METHOD_CALL_TO_SERVICES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
                
























                new \Rector\Transform\ValueObject\ServiceGetterToConstructorInjection(
                    \Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService::class,
                    'getAnotherService',
                    \Rector\Transform\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService::class
                ),
























                
            ]),
        ]]
    );
};
