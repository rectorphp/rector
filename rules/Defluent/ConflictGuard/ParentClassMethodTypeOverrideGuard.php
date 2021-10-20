<?php

declare (strict_types=1);
namespace Rector\Defluent\ConflictGuard;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use Rector\CodingStyle\Reflection\VendorLocationDetector;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use RectorPrefix20211020\Symplify\SmartFileSystem\Normalizer\PathNormalizer;
final class ParentClassMethodTypeOverrideGuard
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\CodingStyle\Reflection\VendorLocationDetector
     */
    private $vendorLocationDetector;
    /**
     * @var \Symplify\SmartFileSystem\Normalizer\PathNormalizer
     */
    private $pathNormalizer;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer
     */
    private $paramTypeInferer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\CodingStyle\Reflection\VendorLocationDetector $vendorLocationDetector, \RectorPrefix20211020\Symplify\SmartFileSystem\Normalizer\PathNormalizer $pathNormalizer, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer $paramTypeInferer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->vendorLocationDetector = $vendorLocationDetector;
        $this->pathNormalizer = $pathNormalizer;
        $this->astResolver = $astResolver;
        $this->paramTypeInferer = $paramTypeInferer;
    }
    public function hasParentMethodOutsideVendor(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        $methodName = $classMethod->name->toString();
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if ($classReflection === $ancestorClassReflection) {
                continue;
            }
            if (!$ancestorClassReflection->hasMethod($methodName)) {
                continue;
            }
            if ($this->vendorLocationDetector->detectFunctionLikeReflection($ancestorClassReflection)) {
                return \true;
            }
        }
        return \false;
    }
    public function isReturnTypeChangeAllowed(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->getParentClassMethod($classMethod);
        // nothing to check
        if (!$parentClassMethodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return \true;
        }
        $classReflection = $parentClassMethodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();
        // probably internal
        if ($fileName === \false) {
            return \false;
        }
        $normalizedFileName = $this->pathNormalizer->normalizePath($fileName);
        return \strpos($normalizedFileName, '/vendor/') === \false;
    }
    public function hasParentClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        return $this->getParentClassMethod($classMethod) instanceof \PHPStan\Reflection\MethodReflection;
    }
    public function hasParentClassMethodDifferentType(\PhpParser\Node\Stmt\ClassMethod $classMethod, int $position, \PHPStan\Type\Type $currentType) : bool
    {
        if ($classMethod->isPrivate()) {
            return \false;
        }
        $methodReflection = $this->getParentClassMethod($classMethod);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return \false;
        }
        $classMethod = $this->astResolver->resolveClassMethodFromMethodReflection($methodReflection);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        if ($classMethod->isPrivate()) {
            return \false;
        }
        if (!isset($classMethod->params[$position])) {
            return \false;
        }
        $inferedType = $this->paramTypeInferer->inferParam($classMethod->params[$position]);
        return \get_class($inferedType) !== \get_class($currentType);
    }
    private function getParentClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PHPStan\Reflection\MethodReflection
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        foreach ($classReflection->getAncestors() as $parentClassReflection) {
            if ($classReflection === $parentClassReflection) {
                continue;
            }
            if (!$parentClassReflection->hasNativeMethod($methodName)) {
                continue;
            }
            return $parentClassReflection->getNativeMethod($methodName);
        }
        return null;
    }
}
