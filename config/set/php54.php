<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class)->configure(['mysqli_param_count' => 'mysqli_stmt_param_count']);
    $services->set(\Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector::class);
    $services->set(\Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector::class);
};
