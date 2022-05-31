<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector;
use Rector\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\Expr\DowngradeUnnecessarilyParenthesizedExpressionRector;
use Rector\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector;
use Rector\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector;
use Rector\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector;
use Rector\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector;
use Rector\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector;
use Rector\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector;
use Rector\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector;
use Rector\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector;
use Rector\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector;
use Rector\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector;
use Rector\DowngradePhp70\Rector\TryCatch\DowngradeCatchThrowableRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_56);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\TryCatch\DowngradeCatchThrowableRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\Instanceof_\DowngradeInstanceofThrowableRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\ClassMethod\DowngradeSelfTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\New_\DowngradeAnonymousClassRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\FuncCall\DowngradeDirnameLevelsRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\FuncCall\DowngradeUncallableValueCallToCallUserFuncRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\GroupUse\SplitGroupedUseImportsRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp70\Rector\Expr\DowngradeUnnecessarilyParenthesizedExpressionRector::class);
};
