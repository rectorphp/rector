<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Type\MixedType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodReturnVendorLockResolver
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function isVendorLocked(ClassMethod $classMethod): bool
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);
        if ($this->isVendorLockedByAncestors($classReflection, $methodName)) {
            return true;
        }

        return $classReflection->isTrait();
    }

    private function isVendorLockedByAncestors(ClassReflection $classReflection, string $methodName): bool
    {
        foreach ($classReflection->getAncestors() as $ancestorClassReflections) {
            if ($ancestorClassReflections === $classReflection) {
                continue;
            }

            $nativeClassReflection = $ancestorClassReflections->getNativeReflection();

            // this should avoid detecting @method as real method
            if (! $nativeClassReflection->hasMethod($methodName)) {
                continue;
            }

            $parentClassMethodReflection = $ancestorClassReflections->getNativeMethod($methodName);
            $parametersAcceptor = $parentClassMethodReflection->getVariants()[0];
            if (! $parametersAcceptor instanceof FunctionVariantWithPhpDocs) {
                continue;
            }

            // here we count only on strict types, not on docs
            return ! $parametersAcceptor->getNativeReturnType() instanceof MixedType;
        }

        return false;
    }
}
