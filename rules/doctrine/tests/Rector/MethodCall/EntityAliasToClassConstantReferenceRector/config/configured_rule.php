<?php

use Rector\Doctrine\Rector\MethodCall\EntityAliasToClassConstantReferenceRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(EntityAliasToClassConstantReferenceRector::class)->call(
        'configure',
        [[
            EntityAliasToClassConstantReferenceRector::ALIASES_TO_NAMESPACES => [
                'App' => 'App\Entity',
            ],
        ]]
    );
};
