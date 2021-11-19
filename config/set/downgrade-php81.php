<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector;
use Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;
use Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);

    $services = $containerConfigurator->services();
    $services->set(DowngradeFinalizePublicClassConstantRector::class);
    $services->set(DowngradeFirstClassCallableSyntaxRector::class);
    $services->set(DowngradeNeverTypeDeclarationRector::class);
    $services->set(DowngradeNewInInitializerRector::class);
    $services->set(DowngradePhp81ResourceReturnToObjectRector::class);
    $services->set(DowngradeReadonlyPropertyRector::class);
};
