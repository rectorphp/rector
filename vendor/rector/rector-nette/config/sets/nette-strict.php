<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

use Rector\Nette\Rector\ClassMethod\RenderMethodParamToTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Nette\Rector\ClassMethod\RenderMethodParamToTypeDeclarationRector::class);
};
