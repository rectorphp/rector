<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use RectorPrefix20220606\Rector\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector;
use RectorPrefix20220606\Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use RectorPrefix20220606\Rector\Removing\Rector\Class_\RemoveInterfacesRector;
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
};
