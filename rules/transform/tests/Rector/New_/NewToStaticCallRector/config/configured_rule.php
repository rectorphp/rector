<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\New_\NewToStaticCallRector::class)->call('configure', [[
        \Rector\Transform\Rector\New_\NewToStaticCallRector::TYPE_TO_STATIC_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\NewToStaticCall(
                \Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass::class,
                \Rector\Transform\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass::class,
                'run'
            ),
























            
        ]),
    ]]);
};
