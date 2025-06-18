<?php

declare (strict_types=1);
namespace Rector\VendorLocker;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\Reflection\ClassReflectionAnalyzer;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\VendorLocker\Exception\UnresolvableClassException;
final class ParentClassMethodTypeOverrideGuard
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ClassReflectionAnalyzer $classReflectionAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver, TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper, ClassReflectionAnalyzer $classReflectionAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classReflectionAnalyzer = $classReflectionAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PHPStan\Reflection\MethodReflection $classMethod
     */
    public function hasParentClassMethod($classMethod) : bool
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
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PHPStan\Reflection\MethodReflection $classMethod
     */
    public function getParentClassMethod($classMethod) : ?MethodReflection
    {
        try {
            return $this->resolveParentClassMethod($classMethod);
        } catch (UnresolvableClassException $exception) {
            return null;
        }
    }
    public function shouldSkipReturnTypeChange(ClassMethod $classMethod, Type $parentType) : bool
    {
        if (!$classMethod->returnType instanceof Node) {
            return \false;
        }
        $currentReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($classMethod->returnType);
        if ($this->typeComparator->isSubtype($currentReturnType, $parentType)) {
            return \true;
        }
        return $this->typeComparator->areTypesEqual($currentReturnType, $parentType);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PHPStan\Reflection\MethodReflection $classMethod
     */
    private function resolveParentClassMethod($classMethod) : ?MethodReflection
    {
        // early got null on private method
        if ($classMethod->isPrivate()) {
            return null;
        }
        $classReflection = $classMethod instanceof ClassMethod ? $this->reflectionResolver->resolveClassReflection($classMethod) : $classMethod->getDeclaringClass();
        if (!$classReflection instanceof ClassReflection) {
            // we can't resolve the class, so we don't know.
            throw new UnresolvableClassException();
        }
        /** @var string $methodName */
        $methodName = $classMethod instanceof ClassMethod ? $this->nodeNameResolver->getName($classMethod) : $classMethod->getName();
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
        foreach ($classReflection->getTraits() as $traitReflection) {
            if (!$traitReflection->hasNativeMethod($methodName)) {
                continue;
            }
            $methodReflection = $traitReflection->getNativeMethod($methodName);
            // any signature on non abstract trait method can be overridden
            if ($methodReflection->isAbstract()) {
                return $methodReflection;
            }
            return null;
        }
        return null;
    }
    private function hasClassParent(ClassReflection $classReflection) : bool
    {
        return $this->classReflectionAnalyzer->resolveParentClassName($classReflection) !== null;
    }
}
