<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector;
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
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, \Rector\Core\ValueObject\PhpVersion::PHP_56);
    $services = $containerConfigurator->services();
    $services->set(\Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector::class);
};
