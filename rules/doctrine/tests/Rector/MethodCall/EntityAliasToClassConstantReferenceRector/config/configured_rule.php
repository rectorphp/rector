<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector::class)->call(
        'configure',
        [[
            \Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector::ALIASES_TO_NAMESPACES => [
                'App' => 'App\Entity',
            ],
        ]]
    );
};
