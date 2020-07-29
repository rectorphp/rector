<?php

declare(strict_types=1);

use Rector\Generic\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector;
use Rector\Generic\Rector\Architecture\DependencyInjection\ReplaceVariableByPropertyFetchRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ActionInjectionToConstructorInjectionRector::class);

    $services->set(ReplaceVariableByPropertyFetchRector::class);
};
