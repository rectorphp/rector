<?php

declare(strict_types=1);

use Rector\PHPOffice\Rector\StaticCall\ChangePdfWriterRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ChangePdfWriterRector::class);
};
