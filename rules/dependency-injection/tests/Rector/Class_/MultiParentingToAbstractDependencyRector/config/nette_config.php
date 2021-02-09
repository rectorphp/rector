<?php

declare(strict_types=1);

use Rector\Core\ValueObject\FrameworkName;
use Rector\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MultiParentingToAbstractDependencyRector::class)
        ->call('configure', [[
            MultiParentingToAbstractDependencyRector::FRAMEWORK => FrameworkName::NETTE,
        ]]);
};
