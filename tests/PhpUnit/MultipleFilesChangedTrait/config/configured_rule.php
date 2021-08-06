<?php

declare(strict_types=1);

use Rector\Core\Tests\PhpUnit\MultipleFilesChangedTrait\Rector\Class_\CreateJsonWithNamesForClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(CreateJsonWithNamesForClassRector::class);
};
