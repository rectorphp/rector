<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ClassMethodManipulator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, ReflectionResolver $reflectionResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isNamedConstructor(ClassMethod $classMethod) : bool
    {
        if (!$this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            return \false;
        }
        $class = $this->betterNodeFinder->findParentType($classMethod, Class_::class);
        if (!$class instanceof Class_) {
            return \false;
        }
        if ($classMethod->isPrivate()) {
            return \true;
        }
        if ($class->isFinal()) {
            return \false;
        }
        return $classMethod->isProtected();
    }
    public function hasParentMethodOrInterfaceMethod(ClassMethod $classMethod, ?string $methodName = null) : bool
    {
        $methodName = $methodName ?? $this->nodeNameResolver->getName($classMethod->name);
        if ($methodName === null) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $possibleNames
     */
    public function addMethodParameterIfMissing(Node $node, ObjectType $objectType, array $possibleNames) : string
    {
        $classMethod = $this->betterNodeFinder->findParentType($node, ClassMethod::class);
        if (!$classMethod instanceof ClassMethod) {
            // or null?
            throw new ShouldNotHappenException();
        }
        foreach ($classMethod->params as $paramNode) {
            if (!$this->nodeTypeResolver->isObjectType($paramNode, $objectType)) {
                continue;
            }
            return $this->nodeNameResolver->getName($paramNode);
        }
        $paramName = $this->resolveName($classMethod, $possibleNames);
        $classMethod->params[] = new Param(new Variable($paramName), null, new FullyQualified($objectType->getClassName()));
        return $paramName;
    }
    /**
     * @param string[] $possibleNames
     */
    private function resolveName(ClassMethod $classMethod, array $possibleNames) : string
    {
        foreach ($possibleNames as $possibleName) {
            foreach ($classMethod->params as $paramNode) {
                if ($this->nodeNameResolver->isName($paramNode, $possibleName)) {
                    continue 2;
                }
            }
            return $possibleName;
        }
        throw new ShouldNotHappenException();
    }
}
