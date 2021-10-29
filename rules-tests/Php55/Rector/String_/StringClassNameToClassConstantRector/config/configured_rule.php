<?php

declare(strict_types=1);

use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(StringClassNameToClassConstantRector::class)
        ->call('configure', [[
            StringClassNameToClassConstantRector::CLASSES_TO_SKIP => ['Nette\*', 'Error', 'Exception'],
        ]]);
};
