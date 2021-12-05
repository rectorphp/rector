<?php

declare (strict_types=1);
namespace Rector\VendorLocker;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;
use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\AstResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use RectorPrefix20211205\Symplify\SmartFileSystem\Normalizer\PathNormalizer;
final class ParentClassMethodTypeOverrideGuard
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\Normalizer\PathNormalizer
     */
    private $pathNormalizer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer
     */
    private $paramTypeInferer;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20211205\Symplify\SmartFileSystem\Normalizer\PathNormalizer $pathNormalizer, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer $paramTypeInferer, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->pathNormalizer = $pathNormalizer;
        $this->astResolver = $astResolver;
        $this->paramTypeInferer = $paramTypeInferer;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function isReturnTypeChangeAllowed(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->getParentClassMethod($classMethod);
        // nothing to check
        if (!$parentClassMethodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return \true;
        }
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($parentClassMethodReflection->getVariants());
        if ($parametersAcceptor instanceof \PHPStan\Reflection\FunctionVariantWithPhpDocs) {
            $parentNativeReturnType = $parametersAcceptor->getNativeReturnType();
            if (!$parentNativeReturnType instanceof \PHPStan\Type\MixedType && $classMethod->returnType !== null) {
                return \false;
            }
        }
        $classReflection = $parentClassMethodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();
        // probably internal
        if ($fileName === null) {
            return \false;
        }
        /*
         * Below verify that both current file name and parent file name is not in the /vendor/, if yes, then allowed.
         * This can happen when rector run into /vendor/ directory while child and parent both are there.
         */
        /** @var Scope $scope */
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        /** @var ClassReflection $currentClassReflection */
        $currentClassReflection = $scope->getClassReflection();
        /** @var string $currentFileName */
        $currentFileName = $currentClassReflection->getFileName();
        // child (current)
        $normalizedCurrentFileName = $this->pathNormalizer->normalizePath($currentFileName);
        $isCurrentNotInVendor = \strpos($normalizedCurrentFileName, '/vendor/') === \false;
        // parent
        $normalizedFileName = $this->pathNormalizer->normalizePath($fileName);
        $isParentNotInVendor = \strpos($normalizedFileName, '/vendor/') === \false;
        return $isCurrentNotInVendor && $isParentNotInVendor;
    }
    /**
     * @return \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|\PhpParser\Node\Name|null
     */
    public function getParentClassMethodNodeType(\PhpParser\Node\Stmt\ClassMethod $classMethod)
    {
        $parentClassMethodReflection = $this->getParentClassMethod($classMethod);
        if (!$parentClassMethodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($parentClassMethodReflection->getVariants());
        if (!$parametersAcceptor instanceof \PHPStan\Reflection\FunctionVariantWithPhpDocs) {
            return null;
        }
        $parentNativeReturnType = $parametersAcceptor->getNativeReturnType();
        if ($parentNativeReturnType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        if ($parentNativeReturnType instanceof \PHPStan\Type\NonexistentParentClassType) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parentNativeReturnType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
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
        $parentClassReflections = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        foreach ($parentClassReflections as $parentClassReflection) {
            if (!$parentClassReflection->hasNativeMethod($methodName)) {
                continue;
            }
            return $parentClassReflection->getNativeMethod($methodName);
        }
        return null;
    }
}
