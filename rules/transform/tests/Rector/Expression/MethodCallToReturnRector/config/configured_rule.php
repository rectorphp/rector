<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\Expression\MethodCallToReturnRector::class)->call('configure', [[
        \Rector\Transform\Rector\Expression\MethodCallToReturnRector::METHOD_CALL_WRAPS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\MethodCallToReturn(
                \Rector\Transform\Tests\Rector\Expression\MethodCallToReturnRector\Source\ReturnDeny::class,
                'deny'
            ),
























            
        ]),
    ]]);
};
