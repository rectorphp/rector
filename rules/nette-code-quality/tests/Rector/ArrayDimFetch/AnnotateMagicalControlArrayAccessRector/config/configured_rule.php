<?php

declare(strict_types=1);

use Rector\NetteCodeQuality\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AnnotateMagicalControlArrayAccessRector::class);
};
