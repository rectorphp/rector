<?php

declare (strict_types=1);
namespace Rector\VendorLocker;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\VendorLocker\Exception\UnresolvableClassException;
final class ParentClassMethodTypeOverrideGuard
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
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
    /**
     * @readonly
     * @var \Rector\Core\Util\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver, TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper, PrivatesAccessor $privatesAccessor)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->privatesAccessor = $privatesAccessor;
    }
    public function hasParentClassMethod(ClassMethod $classMethod) : bool
    {
        try {
            $parentClassMethod = $this->resolveParentClassMethod($classMethod);
            return $parentClassMethod instanceof MethodReflection;
        } catch (UnresolvableClassException $exception) {
            // we don't know all involved parents,
            // marking as parent exists which usually means the method is guarded against overrides.
            return \true;
        }
    }
    public function getParentClassMethod(ClassMethod $classMethod) : ?MethodReflection
    {
        try {
            return $this->resolveParentClassMethod($classMethod);
        } catch (UnresolvableClassException $exception) {
            return null;
        }
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
    private function resolveParentClassMethod(ClassMethod $classMethod) : ?MethodReflection
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            // we can't resolve the class, so we don't know.
            throw new UnresolvableClassException();
        }
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $currentClassReflection = $classReflection;
        while ($this->hasClassParent($currentClassReflection)) {
            $parentClassReflection = $currentClassReflection->getParentClass();
            if (!$parentClassReflection instanceof ClassReflection) {
                // per AST we have a parent class, but our reflection classes are not able to load its class definition/signature
                throw new UnresolvableClassException();
            }
            if ($parentClassReflection->hasNativeMethod($methodName)) {
                return $parentClassReflection->getNativeMethod($methodName);
            }
            $currentClassReflection = $parentClassReflection;
        }
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if (!$interfaceReflection->hasNativeMethod($methodName)) {
                continue;
            }
            return $interfaceReflection->getNativeMethod($methodName);
        }
        return null;
    }
    private function hasClassParent(ClassReflection $classReflection) : bool
    {
        // XXX rework this hack, after https://github.com/phpstan/phpstan-src/pull/2563 landed
        $nativeReflection = $classReflection->getNativeReflection();
        $betterReflectionClass = $this->privatesAccessor->getPrivateProperty($nativeReflection, 'betterReflectionClass');
        /** @var ReflectionClass $betterReflectionClass */
        $parentClassName = $betterReflectionClass->getParentClassName();
        return $parentClassName !== null;
    }
}
