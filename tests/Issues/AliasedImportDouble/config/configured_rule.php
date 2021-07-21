<?php

declare(strict_types=1);

use Rector\Core\Tests\Issues\AliasedImportDouble\Rector\ClassMethod\AddAliasImportRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddAliasImportRector::class);
};
