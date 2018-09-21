<?php declare(strict_types=1);

namespace Rector\Tests\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use SplFileInfo;

/**
 * SplFileInfo->getRealPath() always exists, since it comes from Finder, that finds only existing files
 * This removes many false positives that have to be excluded manually otherwise.
 */
final class SplFileInfoTolerantDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return SplFileInfo::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getRealPath';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        return new StringType();
    }
}
