<?php

declare (strict_types=1);
namespace RectorPrefix202409;

use Rector\DowngradePhp80\Rector\ClassMethod\RemoveReturnTypeDeclarationFromCloneRector;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
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
    $rectorConfig->ruleWithConfiguration(DowngradeAttributeToAnnotationRector::class, [
        // Symfony
        new DowngradeAttributeToAnnotation('Symfony\\Contracts\\Service\\Attribute\\Required', 'required'),
        // Nette
        new DowngradeAttributeToAnnotation('Nette\\DI\\Attributes\\Inject', 'inject'),
        // Jetbrains\PhpStorm\Language under nette/utils
        new DowngradeAttributeToAnnotation('Jetbrains\\PhpStorm\\Language', 'language'),
    ]);
    $rectorConfig->rules([DowngradeNamedArgumentRector::class, DowngradeDereferenceableOperationRector::class, DowngradeUnionTypeTypedPropertyRector::class, DowngradeUnionTypeDeclarationRector::class, DowngradeMixedTypeDeclarationRector::class, DowngradeStaticTypeDeclarationRector::class, DowngradeAbstractPrivateMethodInTraitRector::class, DowngradePropertyPromotionRector::class, DowngradeNonCapturingCatchesRector::class, DowngradeStrContainsRector::class, DowngradeMatchToSwitchRector::class, DowngradeClassOnObjectToGetClassRector::class, DowngradeArbitraryExpressionsSupportRector::class, DowngradeNullsafeToTernaryOperatorRector::class, DowngradeTrailingCommasInParamUseRector::class, DowngradeStrStartsWithRector::class, DowngradeStrEndsWithRector::class, DowngradePhpTokenRector::class, DowngradeThrowExprRector::class, DowngradePhp80ResourceReturnToObjectRector::class, DowngradeReflectionGetAttributesRector::class, DowngradeRecursiveDirectoryIteratorHasChildrenRector::class, DowngradeReflectionPropertyGetDefaultValueRector::class, DowngradeReflectionClassGetConstantsFilterRector::class, DowngradeArrayFilterNullableCallbackRector::class, DowngradeNumberFormatNoFourthArgRector::class, DowngradeStringReturnTypeOnToStringRector::class, DowngradeMixedTypeTypedPropertyRector::class, RemoveReturnTypeDeclarationFromCloneRector::class, DowngradeEnumToConstantListClassRector::class]);
};
