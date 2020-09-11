<?php

declare(strict_types=1);

use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeParamNullableTypeDeclarationRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeReturnNullableTypeDeclarationRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeReturnVoidTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeParamNullableTypeDeclarationRector::class);
    $services->set(DowngradeReturnNullableTypeDeclarationRector::class);
    $services->set(DowngradeReturnVoidTypeDeclarationRector::class);
};
