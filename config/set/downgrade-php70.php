<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector;
use Rector\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector;
use Rector\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector;
use Rector\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector;
use Rector\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector;
use Rector\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_56);

    $services = $containerConfigurator->services();
    $services->set(DowngradeScalarTypeDeclarationRector::class);
    $services->set(DowngradeStrictTypeDeclarationRector::class);
    $services->set(DowngradeSelfTypeDeclarationRector::class);
    $services->set(DowngradeAnonymousClassRector::class);
    $services->set(DowngradeNullCoalesceRector::class);
    $services->set(DowngradeSpaceshipRector::class);
    $services->set(DowngradeDefineArrayConstantRector::class);
    $services->set(DowngradeSessionStartArrayOptionsRector::class);
    $services->set(SplitGroupedUseImportsRector::class);
    $services->set(DowngradeGeneratedScalarTypesRector::class);
};
