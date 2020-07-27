<?php

declare(strict_types=1);

use Rector\PSR4\Rector\MultipleClassFileToPsr4ClassesRector;
use Rector\PSR4\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(NormalizeNamespaceByPSR4ComposerAutoloadRector::class);

    $services->set(MultipleClassFileToPsr4ClassesRector::class);
};
