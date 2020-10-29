<?php

declare(strict_types=1);

use Rector\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReturnFluentChainMethodCallToNormalMethodCallRector::class);
    $services->set(RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class);
};
