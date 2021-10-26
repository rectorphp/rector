<?php

declare(strict_types=1);

namespace Rector\VendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Symplify\SmartFileSystem\Normalizer\PathNormalizer;

final class ParentClassMethodTypeOverrideGuard
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private PathNormalizer $pathNormalizer,
        private AstResolver $astResolver,
        private ParamTypeInferer $paramTypeInferer
    ) {
    }

    public function isReturnTypeChangeAllowed(ClassMethod $classMethod): bool
    {
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->getParentClassMethod($classMethod);

        // nothing to check
        if (! $parentClassMethodReflection instanceof MethodReflection) {
            return true;
        }

        $classReflection = $parentClassMethodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();

        // probably internal
        if ($fileName === false) {
            return false;
        }

        $normalizedFileName = $this->pathNormalizer->normalizePath($fileName);
        return ! str_contains($normalizedFileName, '/vendor/');
    }

    public function hasParentClassMethod(ClassMethod $classMethod): bool
    {
        return $this->getParentClassMethod($classMethod) instanceof MethodReflection;
    }

    public function hasParentClassMethodDifferentType(ClassMethod $classMethod, int $position, Type $currentType): bool
    {
        if ($classMethod->isPrivate()) {
            return false;
        }

        $methodReflection = $this->getParentClassMethod($classMethod);
        if (! $methodReflection instanceof MethodReflection) {
            return false;
        }

        $classMethod = $this->astResolver->resolveClassMethodFromMethodReflection($methodReflection);
        if (! $classMethod instanceof ClassMethod) {
            return false;
        }

        if ($classMethod->isPrivate()) {
            return false;
        }

        if (! isset($classMethod->params[$position])) {
            return false;
        }

        $inferedType = $this->paramTypeInferer->inferParam($classMethod->params[$position]);
        return $inferedType::class !== $currentType::class;
    }

    private function getParentClassMethod(ClassMethod $classMethod): ?MethodReflection
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        foreach ($classReflection->getAncestors() as $parentClassReflection) {
            if ($classReflection === $parentClassReflection) {
                continue;
            }

            if (! $parentClassReflection->hasNativeMethod($methodName)) {
                continue;
            }

            return $parentClassReflection->getNativeMethod($methodName);
        }

        return null;
    }
}
