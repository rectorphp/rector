<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\DowngradePhp80\Rector\ClassMethod\RemoveReturnTypeDeclarationFromCloneRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector;
use Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector;
use Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector;
use Rector\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector;
use Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector;
use Rector\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector;
use Rector\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector;
use Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector;
use Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector;
use Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector;
use Rector\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector;
use Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector;
use Rector\DowngradePhp80\Rector\Property\DowngradeMixedTypeTypedPropertyRector;
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Rector\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector;
use Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Rector\Removing\Rector\Class_\RemoveInterfacesRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_74);
    $rectorConfig->ruleWithConfiguration(RemoveInterfacesRector::class, [
        // @see https://wiki.php.net/rfc/stringable
        'Stringable',
    ]);
    $rectorConfig->rule(DowngradeNamedArgumentRector::class);
    $rectorConfig->ruleWithConfiguration(DowngradeAttributeToAnnotationRector::class, [
        // Symfony
        new DowngradeAttributeToAnnotation('Symfony\\Contracts\\Service\\Attribute\\Required', 'required'),
        // Nette
        new DowngradeAttributeToAnnotation('Nette\\DI\\Attributes\\Inject', 'inject'),
    ]);
    $rectorConfig->rule(DowngradeDereferenceableOperationRector::class);
    $rectorConfig->rule(DowngradeUnionTypeTypedPropertyRector::class);
    $rectorConfig->rule(DowngradeUnionTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeMixedTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeStaticTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeAbstractPrivateMethodInTraitRector::class);
    $rectorConfig->rule(DowngradePropertyPromotionRector::class);
    $rectorConfig->rule(DowngradeNonCapturingCatchesRector::class);
    $rectorConfig->rule(DowngradeStrContainsRector::class);
    $rectorConfig->rule(DowngradeMatchToSwitchRector::class);
    $rectorConfig->rule(DowngradeClassOnObjectToGetClassRector::class);
    $rectorConfig->rule(DowngradeArbitraryExpressionsSupportRector::class);
    $rectorConfig->rule(DowngradeNullsafeToTernaryOperatorRector::class);
    $rectorConfig->rule(DowngradeTrailingCommasInParamUseRector::class);
    $rectorConfig->rule(DowngradeStrStartsWithRector::class);
    $rectorConfig->rule(DowngradeStrEndsWithRector::class);
    $rectorConfig->rule(DowngradePhpTokenRector::class);
    $rectorConfig->rule(DowngradeThrowExprRector::class);
    $rectorConfig->rule(DowngradePhp80ResourceReturnToObjectRector::class);
    $rectorConfig->rule(DowngradeReflectionGetAttributesRector::class);
    $rectorConfig->rule(DowngradeRecursiveDirectoryIteratorHasChildrenRector::class);
    $rectorConfig->rule(DowngradeReflectionPropertyGetDefaultValueRector::class);
    $rectorConfig->rule(DowngradeReflectionClassGetConstantsFilterRector::class);
    $rectorConfig->rule(DowngradeArrayFilterNullableCallbackRector::class);
    $rectorConfig->rule(DowngradeNumberFormatNoFourthArgRector::class);
    $rectorConfig->rule(DowngradeStringReturnTypeOnToStringRector::class);
    $rectorConfig->rule(DowngradeMixedTypeTypedPropertyRector::class);
    $rectorConfig->rule(RemoveReturnTypeDeclarationFromCloneRector::class);
    $rectorConfig->rule(DowngradeEnumToConstantListClassRector::class);
};
