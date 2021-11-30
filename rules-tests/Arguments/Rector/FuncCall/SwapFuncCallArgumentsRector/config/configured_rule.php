<?php

declare(strict_types=1);

use Rector\Arguments\Rector\FuncCall\SwapFuncCallArgumentsRector;
use Rector\Arguments\ValueObject\SwapFuncCallArguments;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(SwapFuncCallArgumentsRector::class)
        ->configure([new SwapFuncCallArguments('some_function', [1, 0])]);
};
