<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\DependencyInjection\Rector\Class_\ActionInjectionToConstructorInjectionRector;
use Rector\DependencyInjection\Rector\Variable\ReplaceVariableByPropertyFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ . '/../xml/services.xml');

    $services = $containerConfigurator->services();
    $services->set(ActionInjectionToConstructorInjectionRector::class);
    $services->set(ReplaceVariableByPropertyFetchRector::class);
};
