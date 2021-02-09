<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\ClassMethod\WrapReturnRector::class)->call('configure', [[
        \Rector\Generic\Rector\ClassMethod\WrapReturnRector::TYPE_METHOD_WRAPS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Generic\ValueObject\WrapReturn(
                \Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass::class,
                'getItem',
                true
            ),
























            
        ]),
    ]]);
};
