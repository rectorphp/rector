<?php

declare(strict_types=1);

use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeParamDeclarationRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeReturnDeclarationRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeReturnDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeNullableTypeParamDeclarationRector::class);
    $services->set(DowngradeNullableTypeReturnDeclarationRector::class);
    $services->set(DowngradeVoidTypeReturnDeclarationRector::class);
};
