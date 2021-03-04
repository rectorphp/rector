<?php

declare(strict_types=1);

use Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(WrapVariableVariableNameInCurlyBracesRector::class);
};
