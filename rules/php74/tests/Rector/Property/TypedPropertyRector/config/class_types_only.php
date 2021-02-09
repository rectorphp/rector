<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php74\Rector\Property\TypedPropertyRector::class)->call('configure', [[
        \Rector\Php74\Rector\Property\TypedPropertyRector::CLASS_LIKE_TYPE_ONLY => true,
    ]]);
};
