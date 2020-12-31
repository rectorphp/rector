<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FinalizeClassesWithoutChildrenRector::class);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/tests/Finalize/Fixture/Source'
    ]);
};
