<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

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
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Rector\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector;
use Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Rector\Removing\Rector\Class_\RemoveInterfacesRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_74);
    $rectorConfig->ruleWithConfiguration(\Rector\Removing\Rector\Class_\RemoveInterfacesRector::class, [
        // @see https://wiki.php.net/rfc/stringable
        'Stringable',
    ]);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector::class, [
        // Symfony
        new \Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation('Symfony\\Contracts\\Service\\Attribute\\Required', 'required'),
        // Nette
        new \Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation('Nette\\DI\\Attributes\\Inject', 'inject'),
    ]);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector::class);
};
