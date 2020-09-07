<?php

declare(strict_types=1);

use Rector\Downgrade\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;
use Rector\Downgrade\Rector\FunctionLike\DowngradeParamObjectTypeDeclarationRector;
use Rector\Downgrade\Rector\FunctionLike\DowngradeReturnObjectTypeDeclarationRector;
use Rector\Downgrade\Rector\Property\DowngradeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DowngradeParamObjectTypeDeclarationRector::class);

    $services->set(DowngradeReturnObjectTypeDeclarationRector::class);

    $services->set(DowngradeTypedPropertyRector::class);

    $services->set(DowngradeNullCoalescingOperatorRector::class);
};
