<?php

declare (strict_types=1);
namespace Rector\Privatization\Guard;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Enum\ObjectReference;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeManipulator\PropertyManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Reflection\ClassReflectionAnalyzer;
final class ParentPropertyLookupGuard
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private PropertyFetchAnalyzer $propertyFetchAnalyzer;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private PropertyManipulator $propertyManipulator;
    /**
     * @readonly
     */
    private ClassReflectionAnalyzer $classReflectionAnalyzer;
    /**
     * Symfony Console Command reserved static properties, read by the parent class via reflection
     * even when not declared on the parent class itself
     * @see https://github.com/symfony/console/blob/7.1/Command/Command.php
     *
     * @var string[]
     */
    private const SYMFONY_COMMAND_RESERVED_PROPERTY_NAMES = ['defaultName', 'defaultDescription'];
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer, AstResolver $astResolver, PropertyManipulator $propertyManipulator, ClassReflectionAnalyzer $classReflectionAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->astResolver = $astResolver;
        $this->propertyManipulator = $propertyManipulator;
        $this->classReflectionAnalyzer = $classReflectionAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|string $property
     */
    public function isLegal($property, ?ClassReflection $classReflection): bool
    {
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if ($classReflection->isAnonymous()) {
            return \false;
        }
        $propertyName = $property instanceof Property ? $this->nodeNameResolver->getName($property) : $property;
        if ($this->propertyManipulator->isUsedByTrait($classReflection, $propertyName)) {
            return \false;
        }
        if ($this->isSymfonyCommandReservedProperty($classReflection, $propertyName)) {
            return \false;
        }
        $parentClassName = $this->classReflectionAnalyzer->resolveParentClassName($classReflection);
        if ($parentClassName === null) {
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
    private function isSymfonyCommandReservedProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if (!in_array($propertyName, self::SYMFONY_COMMAND_RESERVED_PROPERTY_NAMES, \true)) {
            return \false;
        }
        return $classReflection->is('Symfony\Component\Console\Command\Command');
    }
    private function isFoundInParentClassMethods(ClassReflection $parentClassReflection, string $propertyName, string $className): bool
    {
        $classLike = $this->astResolver->resolveClassFromClassReflection($parentClassReflection);
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
    private function isFoundInMethodStmts(array $stmts, string $propertyName, string $className): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $subNode) use ($propertyName, $className): bool {
            if (!$this->propertyFetchAnalyzer->isPropertyFetch($subNode)) {
                return \false;
            }
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
    private function isGuardedByParents(array $parentClassReflections, string $propertyName, string $className): bool
    {
        foreach ($parentClassReflections as $parentClassReflection) {
            if ($parentClassReflection->hasInstanceProperty($propertyName)) {
                return \false;
            }
            if ($parentClassReflection->hasStaticProperty($propertyName)) {
                return \false;
            }
            if ($this->isFoundInParentClassMethods($parentClassReflection, $propertyName, $className)) {
                return \false;
            }
        }
        return \true;
    }
}
