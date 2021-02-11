<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\ProjectType;
use Rector\Privatization\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PROJECT_TYPE, ProjectType::OPEN_SOURCE);

    $services = $containerConfigurator->services();
    $services->set(PrivatizeLocalPropertyToPrivatePropertyRector::class);
};
