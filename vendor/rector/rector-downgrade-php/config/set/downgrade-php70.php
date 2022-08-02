<?php

declare (strict_types=1);
namespace RectorPrefix202208;

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
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_56);
    $rectorConfig->rule(DowngradeCatchThrowableRector::class);
    $rectorConfig->rule(DowngradeInstanceofThrowableRector::class);
    $rectorConfig->rule(DowngradeScalarTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeThrowableTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeStrictTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeSelfTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeAnonymousClassRector::class);
    $rectorConfig->rule(DowngradeNullCoalesceRector::class);
    $rectorConfig->rule(DowngradeSpaceshipRector::class);
    $rectorConfig->rule(DowngradeDefineArrayConstantRector::class);
    $rectorConfig->rule(DowngradeDirnameLevelsRector::class);
    $rectorConfig->rule(DowngradeSessionStartArrayOptionsRector::class);
    $rectorConfig->rule(DowngradeUncallableValueCallToCallUserFuncRector::class);
    $rectorConfig->rule(SplitGroupedUseImportsRector::class);
    $rectorConfig->rule(DowngradeClosureCallRector::class);
    $rectorConfig->rule(DowngradeParentTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeMethodCallOnCloneRector::class);
    $rectorConfig->rule(DowngradeUnnecessarilyParenthesizedExpressionRector::class);
};
