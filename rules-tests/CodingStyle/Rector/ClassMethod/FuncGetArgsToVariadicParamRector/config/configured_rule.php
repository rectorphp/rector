<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(FuncGetArgsToVariadicParamRector::class);
};
