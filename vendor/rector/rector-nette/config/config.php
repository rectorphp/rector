<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use Rector\Nette\Rector\Latte\RenameMethodLatteRector;
use Rector\Nette\Rector\Neon\RenameMethodNeonRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/packages.php');
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\Nette\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Contract', __DIR__ . '/../src/Rector', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/Kdyby/Rector', __DIR__ . '/../src/Kdyby/ValueObject']);
    $services->set(\Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector::class);
    $services->set(\Rector\Nette\Rector\Neon\RenameMethodNeonRector::class);
    $services->set(\Rector\Nette\Rector\Latte\RenameMethodLatteRector::class);
};
