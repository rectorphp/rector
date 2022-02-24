<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\Configuration\Option;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
    ]);

    $services = $containerConfigurator->services();
    $services->set(RenameClassRector::class)
        ->configure(['DateTime' => 'DateTimeInterface']);
};
