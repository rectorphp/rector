<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\DowngradePhp81\Rector\FuncCall\DowngradeHashAlgorithmXxHashRector;
use Rector\DowngradePhp81\Rector\LNumber\DowngradeOctalNumberRector;
use Rector\DowngradePhp81\Rector\MethodCall\DowngradeIsEnumRector;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\DowngradePhp81\Rector\Array_\DowngradeArraySpreadStringKeyRector;
use Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector;
use Rector\DowngradePhp81\Rector\FuncCall\DowngradeFirstClassCallableSyntaxRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector;
use Rector\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector;
use Rector\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector;
use Rector\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector;
use Rector\DowngradePhp81\Rector\StmtsAwareInterface\DowngradeSetAccessibleReflectionPropertyRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_80);
    $rectorConfig->rules([DowngradeFinalizePublicClassConstantRector::class, DowngradeFirstClassCallableSyntaxRector::class, DowngradeNeverTypeDeclarationRector::class, DowngradePureIntersectionTypeRector::class, DowngradeNewInInitializerRector::class, DowngradePhp81ResourceReturnToObjectRector::class, DowngradeReadonlyPropertyRector::class, DowngradeArraySpreadStringKeyRector::class, DowngradeArrayIsListRector::class, DowngradeSetAccessibleReflectionPropertyRector::class, DowngradeIsEnumRector::class, DowngradeOctalNumberRector::class, DowngradeHashAlgorithmXxHashRector::class]);
    // @see https://php.watch/versions/8.1/internal-method-return-types#reflection
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('ReflectionFunction', 'hasTentativeReturnType', 'hasReturnType'), new MethodCallRename('ReflectionFunction', 'getTentativeReturnType', 'getReturnType'), new MethodCallRename('ReflectionMethod', 'hasTentativeReturnType', 'hasReturnType'), new MethodCallRename('ReflectionMethod', 'getTentativeReturnType', 'getReturnType')]);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        // @see https://php.watch/versions/8.1/enums#enum-exists
        'enum_exists' => 'class_exists',
    ]);
};
