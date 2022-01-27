<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ExceptionHandlerTypehintRector::class);
    $services->set(CatchExceptionNameMatchingTypeRector::class);
    $services->set(AddDefaultValueForUndefinedVariableRector::class);
};
