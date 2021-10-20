<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector;
use Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector;
use Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector;
use Rector\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector;
use Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector;
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Rector\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector;
use Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Rector\Removing\Rector\Class_\RemoveInterfacesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, \Rector\Core\ValueObject\PhpVersion::PHP_74);
    $services = $containerConfigurator->services();
    $services->set(\Rector\Removing\Rector\Class_\RemoveInterfacesRector::class)->call('configure', [[\Rector\Removing\Rector\Class_\RemoveInterfacesRector::INTERFACES_TO_REMOVE => [
        // @see https://wiki.php.net/rfc/stringable
        'Stringable',
    ]]]);
    $services->set(\Rector\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector::class)->call('configure', [[\Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector::ATTRIBUTE_TO_ANNOTATION => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
        // Symfony
        new \Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation('Symfony\\Contracts\\Service\\Attribute\\Required', 'required'),
        // Nette
        new \Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation('Nette\\DI\\Attributes\\Inject', 'inject'),
    ])]]);
    $services->set(\Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector::class);
};
