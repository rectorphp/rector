<?php

declare(strict_types=1);

use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddVoidReturnTypeWhereNoReturnRector::class)->call('configure', [[
        AddVoidReturnTypeWhereNoReturnRector::USE_PHPDOC => true,
    ]]);
};
