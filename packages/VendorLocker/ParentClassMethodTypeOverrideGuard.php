<?php

declare(strict_types=1);

namespace Rector\VendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Symplify\SmartFileSystem\Normalizer\PathNormalizer;

final class ParentClassMethodTypeOverrideGuard
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly PathNormalizer $pathNormalizer,
        private readonly AstResolver $astResolver,
        private readonly ParamTypeInferer $paramTypeInferer
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

        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($parentClassMethodReflection->getVariants());
        if ($parametersAcceptor instanceof FunctionVariantWithPhpDocs && ! $parametersAcceptor->getNativeReturnType() instanceof MixedType) {
            return false;
        }

        $classReflection = $parentClassMethodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();

        // probably internal
        if ($fileName === null) {
            return false;
        }

        /*
         * Below verify that both current file name and parent file name is not in the /vendor/, if yes, then allowed.
         * This can happen when rector run into /vendor/ directory while child and parent both are there.
         *
         *  @see https://3v4l.org/Rc0RF#v8.0.13
         *
         *     - both in /vendor/ -> allowed
         *     - one of them in /vendor/ -> not allowed
         *     - both not in /vendor/ -> allowed
         */
        /** @var Scope $scope */
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        /** @var ClassReflection $currentClassReflection */
        $currentClassReflection = $scope->getClassReflection();
        /** @var string $currentFileName */
        $currentFileName = $currentClassReflection->getFileName();

        // child (current)
        $normalizedCurrentFileName = $this->pathNormalizer->normalizePath($currentFileName);
        $isCurrentInVendor = str_contains($normalizedCurrentFileName, '/vendor/');

        // parent
        $normalizedFileName = $this->pathNormalizer->normalizePath($fileName);
        $isParentInVendor = str_contains($normalizedFileName, '/vendor/');

        return ($isCurrentInVendor && $isParentInVendor) || (! $isCurrentInVendor && ! $isParentInVendor);
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

        $parentClassReflections = array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        foreach ($parentClassReflections as $parentClassReflection) {
            if (! $parentClassReflection->hasNativeMethod($methodName)) {
                continue;
            }

            return $parentClassReflection->getNativeMethod($methodName);
        }

        return null;
    }
}
