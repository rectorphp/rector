<?php

declare (strict_types=1);
namespace Rector\VendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use RectorPrefix202208\Symplify\SmartFileSystem\Normalizer\PathNormalizer;
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
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(NodeNameResolver $nodeNameResolver, PathNormalizer $pathNormalizer, AstResolver $astResolver, ParamTypeInferer $paramTypeInferer, ReflectionResolver $reflectionResolver, TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->pathNormalizer = $pathNormalizer;
        $this->astResolver = $astResolver;
        $this->paramTypeInferer = $paramTypeInferer;
        $this->reflectionResolver = $reflectionResolver;
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function isReturnTypeChangeAllowed(ClassMethod $classMethod) : bool
    {
        // __construct cannot declare a return type
        // so the return type change is not allowed
        if ($this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            return \false;
        }
        // make sure return type is not protected by parent contract
        $parentClassMethodReflection = $this->getParentClassMethod($classMethod);
        // nothing to check
        if (!$parentClassMethodReflection instanceof MethodReflection) {
            return \true;
        }
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($parentClassMethodReflection->getVariants());
        if ($parametersAcceptor instanceof FunctionVariantWithPhpDocs && !$parametersAcceptor->getNativeReturnType() instanceof MixedType) {
            return \false;
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
         *
         *  @see https://3v4l.org/Rc0RF#v8.0.13
         *
         *     - both in /vendor/ -> allowed
         *     - one of them in /vendor/ -> not allowed
         *     - both not in /vendor/ -> allowed
         */
        /** @var ClassReflection $currentClassReflection */
        $currentClassReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        /** @var string $currentFileName */
        $currentFileName = $currentClassReflection->getFileName();
        // child (current)
        $normalizedCurrentFileName = $this->pathNormalizer->normalizePath($currentFileName);
        $isCurrentInVendor = \strpos($normalizedCurrentFileName, '/vendor/') !== \false;
        // parent
        $normalizedFileName = $this->pathNormalizer->normalizePath($fileName);
        $isParentInVendor = \strpos($normalizedFileName, '/vendor/') !== \false;
        return $isCurrentInVendor && $isParentInVendor || !$isCurrentInVendor && !$isParentInVendor;
    }
    public function hasParentClassMethod(ClassMethod $classMethod) : bool
    {
        return $this->getParentClassMethod($classMethod) instanceof MethodReflection;
    }
    public function hasParentClassMethodDifferentType(ClassMethod $classMethod, int $position, Type $currentType) : bool
    {
        if ($classMethod->isPrivate()) {
            return \false;
        }
        $methodReflection = $this->getParentClassMethod($classMethod);
        if (!$methodReflection instanceof MethodReflection) {
            return \false;
        }
        $classMethod = $this->astResolver->resolveClassMethodFromMethodReflection($methodReflection);
        if (!$classMethod instanceof ClassMethod) {
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
    public function getParentClassMethod(ClassMethod $classMethod) : ?MethodReflection
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $parentClassReflections = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        foreach ($parentClassReflections as $parentClassReflection) {
            if (!$parentClassReflection->hasNativeMethod($methodName)) {
                continue;
            }
            return $parentClassReflection->getNativeMethod($methodName);
        }
        return null;
    }
    public function shouldSkipReturnTypeChange(ClassMethod $classMethod, Type $parentType) : bool
    {
        if ($classMethod->returnType === null) {
            return \false;
        }
        $currentReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($classMethod->returnType);
        if ($this->typeComparator->isSubtype($currentReturnType, $parentType)) {
            return \true;
        }
        return $this->typeComparator->areTypesEqual($currentReturnType, $parentType);
    }
}
