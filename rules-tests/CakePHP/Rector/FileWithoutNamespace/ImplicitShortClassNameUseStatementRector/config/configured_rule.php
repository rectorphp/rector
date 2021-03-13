<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\FileWithoutNamespace\ImplicitShortClassNameUseStatementRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ImplicitShortClassNameUseStatementRector::class);
};
