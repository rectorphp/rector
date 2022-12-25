<?php

declare (strict_types=1);
namespace Rector\Privatization\Guard;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class ParentPropertyLookupGuard
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, ReflectionResolver $reflectionResolver, NodeNameResolver $nodeNameResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer, AstResolver $astResolver, PropertyManipulator $propertyManipulator, ClassAnalyzer $classAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->astResolver = $astResolver;
        $this->propertyManipulator = $propertyManipulator;
        $this->classAnalyzer = $classAnalyzer;
    }
    public function isLegal(Property $property, Class_ $class) : bool
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->propertyManipulator->isUsedByTrait($classReflection, $propertyName)) {
            return \false;
        }
        if ($class->extends === null) {
            return \true;
        }
        $className = $classReflection->getName();
        $parentClassReflections = $classReflection->getParents();
        // parent class not autoloaded
        if ($parentClassReflections === []) {
            return \false;
        }
        return $this->isGuardedByParents($parentClassReflections, $propertyName, $className);
    }
    private function isFoundInParentClassMethods(ClassReflection $parentClassReflection, string $propertyName, string $className) : bool
    {
        $classLike = $this->astResolver->resolveClassFromName($parentClassReflection->getName());
        if (!$classLike instanceof Class_) {
            return \false;
        }
        $methods = $classLike->getMethods();
        foreach ($methods as $method) {
            $isFound = $this->isFoundInMethodStmts((array) $method->stmts, $propertyName, $className);
            if ($isFound) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isFoundInMethodStmts(array $stmts, string $propertyName, string $className) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $subNode) use($propertyName, $className) : bool {
            if (!$this->propertyFetchAnalyzer->isPropertyFetch($subNode)) {
                return \false;
            }
            /** @var PropertyFetch|StaticPropertyFetch $subNode */
            if ($subNode instanceof PropertyFetch) {
                if (!$subNode->var instanceof Variable) {
                    return \false;
                }
                if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                    return \false;
                }
                return $this->nodeNameResolver->isName($subNode, $propertyName);
            }
            if (!$this->nodeNameResolver->isNames($subNode->class, [ObjectReference::SELF, ObjectReference::STATIC, $className])) {
                return \false;
            }
            return $this->nodeNameResolver->isName($subNode->name, $propertyName);
        });
    }
    /**
     * @param ClassReflection[] $parentClassReflections
     */
    private function isGuardedByParents(array $parentClassReflections, string $propertyName, string $className) : bool
    {
        foreach ($parentClassReflections as $parentClassReflection) {
            if ($parentClassReflection->hasProperty($propertyName)) {
                return \false;
            }
            if ($this->isFoundInParentClassMethods($parentClassReflection, $propertyName, $className)) {
                return \false;
            }
        }
        return \true;
    }
}
