<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector::class)->call('configure', [[
        \Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector::DETECTED_TRAITS => [
            \Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\Source\SomeTrait::class,
        ],
    ]]);
};
