<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Symfony\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::IMPORT_DOC_BLOCKS, true);

    $services = $containerConfigurator->services();
    $services->set(ReplaceSensioRouteAnnotationWithSymfonyRector::class);
};
