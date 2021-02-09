<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Generic\Rector\ClassMethod\SingleToManyMethodRector::class)->call('configure', [[
        \Rector\Generic\Rector\ClassMethod\SingleToManyMethodRector::SINGLES_TO_MANY_METHODS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Generic\ValueObject\SingleToManyMethod(
                \Rector\Generic\Tests\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface::class,
                'getNode',
                'getNodes'
            ),
























            
        ]),
    ]]);
};
