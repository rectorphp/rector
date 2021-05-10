<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use Rector\Core\Configuration\Option;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHPSTAN_FOR_RECTOR_PATH, __DIR__ . '/../phpstan-for-rector.neon');
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('Rector\\Nette\\', __DIR__ . '/../src')->exclude([__DIR__ . '/../src/Contract', __DIR__ . '/../src/Rector', __DIR__ . '/../src/ValueObject', __DIR__ . '/../src/Kdyby/Rector', __DIR__ . '/../src/Kdyby/ValueObject']);
    $services->set(RenameClassNonPhpRector::class);
};
