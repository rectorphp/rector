<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\Namespace_\AppUsesStaticCallToUseStatementRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AppUsesStaticCallToUseStatementRector::class);
};
