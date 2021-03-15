<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp74\Rector\Array_\DowngradeArraySpreadRector;
use Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector;
use Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;
use Rector\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector;
use Rector\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector;
use Rector\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector;
use Rector\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector;
use Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_73);

    $services = $containerConfigurator->services();
    $services->set(DowngradeTypedPropertyRector::class);
    $services->set(ArrowFunctionToAnonymousFunctionRector::class);
    $services->set(DowngradeCovariantReturnTypeRector::class);
    $services->set(DowngradeContravariantArgumentTypeRector::class);
    $services->set(DowngradeNullCoalescingOperatorRector::class);
    $services->set(DowngradeNumericLiteralSeparatorRector::class);
    $services->set(DowngradeStripTagsCallWithArrayRector::class);
    $services->set(DowngradeArraySpreadRector::class);
    $services->set(DowngradeArrayMergeCallWithoutArgumentsRector::class);
    $services->set(DowngradeFreadFwriteFalsyToNegationRector::class);
    $services->set(DowngradeSelfTypeDeclarationRector::class);
};
