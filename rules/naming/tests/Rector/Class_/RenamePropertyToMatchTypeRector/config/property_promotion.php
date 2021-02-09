<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::PROPERTY_PROMOTION);

    $services = $containerConfigurator->services();
    $services->set(RenamePropertyToMatchTypeRector::class);
};
