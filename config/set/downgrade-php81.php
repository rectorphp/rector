<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector;
use Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector;
use Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;
use Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);

    $services = $rectorConfig->services();
    $services->set(DowngradeFinalizePublicClassConstantRector::class);
    $services->set(DowngradeFirstClassCallableSyntaxRector::class);
    $services->set(DowngradeNeverTypeDeclarationRector::class);
    $services->set(DowngradePureIntersectionTypeRector::class);
    $services->set(DowngradeNewInInitializerRector::class);
    $services->set(DowngradePhp81ResourceReturnToObjectRector::class);
    $services->set(DowngradeReadonlyPropertyRector::class);
    $services->set(DowngradeArraySpreadStringKeyRector::class);
    $services->set(DowngradeArrayIsListRector::class);
};
