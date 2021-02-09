<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector;
use Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector;
use Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector;
use Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector;
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(DowngradeUnionTypeTypedPropertyRector::class);
    $services->set(DowngradeUnionTypeReturnDeclarationRector::class);
    $services->set(DowngradeUnionTypeParamDeclarationRector::class);
    $services->set(DowngradeParamMixedTypeDeclarationRector::class);
    $services->set(DowngradeReturnMixedTypeDeclarationRector::class);
    $services->set(DowngradeReturnStaticTypeDeclarationRector::class);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

    // skip root namespace classes, like \DateTime or \Exception [default: true]
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);

    // skip classes used in PHP DocBlocks, like in /** @var \Some\Class */ [default: true]
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);

    $services->set(DowngradePropertyPromotionRector::class);
    $services->set(DowngradeNonCapturingCatchesRector::class);
    $services->set(DowngradeMatchToSwitchRector::class);
    $services->set(DowngradeClassOnObjectToGetClassRector::class);
    $services->set(DowngradeNullsafeToTernaryOperatorRector::class);
    $services->set(DowngradeTrailingCommasInParamUseRector::class);
};
