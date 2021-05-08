<?php

declare(strict_types=1);

use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use Rector\DowngradePhp71\Rector\FunctionLike\DowngradeNullableTypeDeclarationRector;
use Rector\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector;
use Rector\Set\ValueObject\DowngradeSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
//    $services->set(DowngradeNullableTypeDeclarationRector::class);
//    $services->set(DowngradeScalarTypeDeclarationRector::class);
//    $services->set(DowngradeParameterTypeWideningRector::class);
//
    $containerConfigurator->import(DowngradeSetList::PHP_71);
    $containerConfigurator->import(DowngradeSetList::PHP_72);
    $containerConfigurator->import(DowngradeSetList::PHP_73);
    $containerConfigurator->import(DowngradeSetList::PHP_74);
    $containerConfigurator->import(DowngradeSetList::PHP_80);
};
