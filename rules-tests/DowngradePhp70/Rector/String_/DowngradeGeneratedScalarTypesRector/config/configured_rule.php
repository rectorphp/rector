<?php

declare(strict_types=1);

use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeGeneratedScalarTypesRector::class);

    // dependendent rules
    $services->set(DowngradeScalarTypeDeclarationRector::class);
    $services->set(DowngradeVoidTypeDeclarationRector::class);
};
