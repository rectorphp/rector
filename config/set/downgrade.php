<?php

declare(strict_types=1);

use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector;
use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector;
use Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector;
use Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;
use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeToDocBlockRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeParamObjectTypeDeclarationRector::class);
    $services->set(DowngradeReturnObjectTypeDeclarationRector::class);
    $services->set(DowngradeTypedPropertyRector::class);
    $services->set(ArrowFunctionToAnonymousFunctionRector::class);
    $services->set(DowngradeNullCoalescingOperatorRector::class);
    $services->set(DowngradeUnionTypeToDocBlockRector::class);
};
