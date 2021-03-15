<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp71\Rector\Array_\SymmetricArrayDestructuringToListRector;
use Rector\DowngradePhp71\Rector\ClassConst\DowngradeClassConstantVisibilityRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeVoidTypeDeclarationRector;
use Rector\DowngradePhp71\Rector\String_\DowngradeNegativeStringOffsetToStrlenRector;
use Rector\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_70);

    $services = $containerConfigurator->services();
    $services->set(DowngradeNullableTypeDeclarationRector::class);
    $services->set(DowngradeVoidTypeDeclarationRector::class);
    $services->set(DowngradeClassConstantVisibilityRector::class);
    $services->set(DowngradePipeToMultiCatchExceptionRector::class);
    $services->set(SymmetricArrayDestructuringToListRector::class);
    $services->set(DowngradeNegativeStringOffsetToStrlenRector::class);
};
