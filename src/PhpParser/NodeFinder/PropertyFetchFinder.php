<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class PropertyFetchFinder
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
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver, AstResolver $astResolver, NodeTypeResolver $nodeTypeResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    /**
     * @return array<PropertyFetch|StaticPropertyFetch>
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrPromotedParam
     */
    public function findPrivatePropertyFetches(Class_ $class, $propertyOrPromotedParam) : array
    {
        $propertyName = $this->resolvePropertyName($propertyOrPromotedParam);
        if ($propertyName === null) {
            return [];
        }
        $classReflection = $this->reflectionResolver->resolveClassAndAnonymousClass($class);
        $nodes = [$class];
        $nodesTrait = $this->astResolver->parseClassReflectionTraits($classReflection);
        $hasTrait = $nodesTrait !== [];
        $nodes = \array_merge($nodes, $nodesTrait);
        return $this->findPropertyFetchesInClassLike($class, $nodes, $propertyName, $hasTrait);
    }
    /**
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    public function findLocalPropertyFetchesByName(Class_ $class, string $paramName) : array
    {
        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->findInstancesOf($class, [PropertyFetch::class, StaticPropertyFetch::class]);
        $foundPropertyFetches = [];
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->propertyFetchAnalyzer->isLocalPropertyFetchName($propertyFetch, $paramName)) {
                $foundPropertyFetches[] = $propertyFetch;
            }
        }
        return $foundPropertyFetches;
    }
    /**
     * @return ArrayDimFetch[]
     */
    public function findLocalPropertyArrayDimFetchesAssignsByName(Class_ $class, Property $property) : array
    {
        $propertyName = $this->nodeNameResolver->getName($property);
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf($class, Assign::class);
        $propertyArrayDimFetches = [];
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof ArrayDimFetch) {
                continue;
            }
            $dimFetchVar = $assign->var;
            if (!$dimFetchVar->var instanceof PropertyFetch && !$dimFetchVar->var instanceof StaticPropertyFetch) {
                continue;
            }
            if (!$this->propertyFetchAnalyzer->isLocalPropertyFetchName($dimFetchVar->var, $propertyName)) {
                continue;
            }
            $propertyArrayDimFetches[] = $dimFetchVar;
        }
        return $propertyArrayDimFetches;
    }
    public function isLocalPropertyFetchByName(Expr $expr, string $propertyName) : bool
    {
        if (!$expr instanceof PropertyFetch) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr->name, $propertyName)) {
            return \false;
        }
        return $this->nodeNameResolver->isName($expr->var, 'this');
    }
    /**
     * @param Stmt[] $stmts
     * @return PropertyFetch[]|StaticPropertyFetch[]
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_ $class
     */
    private function findPropertyFetchesInClassLike($class, array $stmts, string $propertyName, bool $hasTrait) : array
    {
        /** @var PropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->findInstanceOf($stmts, PropertyFetch::class);
        /** @var PropertyFetch[] $matchingPropertyFetches */
        $matchingPropertyFetches = \array_filter($propertyFetches, function (PropertyFetch $propertyFetch) use($propertyName, $class, $hasTrait) : bool {
            if ($this->isInAnonymous($propertyFetch, $class, $hasTrait)) {
                return \false;
            }
            return $this->isNamePropertyNameEquals($propertyFetch, $propertyName, $class);
        });
        /** @var StaticPropertyFetch[] $staticPropertyFetches */
        $staticPropertyFetches = $this->betterNodeFinder->findInstanceOf($stmts, StaticPropertyFetch::class);
        /** @var StaticPropertyFetch[] $matchingStaticPropertyFetches */
        $matchingStaticPropertyFetches = \array_filter($staticPropertyFetches, function (StaticPropertyFetch $staticPropertyFetch) use($propertyName) : bool {
            return $this->nodeNameResolver->isName($staticPropertyFetch->name, $propertyName);
        });
        return \array_merge($matchingPropertyFetches, $matchingStaticPropertyFetches);
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_ $class
     */
    private function isInAnonymous(PropertyFetch $propertyFetch, $class, bool $hasTrait) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($propertyFetch);
        if (!$classReflection instanceof ClassReflection || !$classReflection->isClass()) {
            return \false;
        }
        if ($classReflection->getName() === $this->nodeNameResolver->getName($class)) {
            return \false;
        }
        return !$hasTrait;
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_ $class
     */
    private function isNamePropertyNameEquals(PropertyFetch $propertyFetch, string $propertyName, $class) : bool
    {
        // early check if property fetch name is not equals with property name
        // so next check is check var name and var type only
        if (!$this->isLocalPropertyFetchByName($propertyFetch, $propertyName)) {
            return \false;
        }
        $propertyFetchVarType = $this->nodeTypeResolver->getType($propertyFetch->var);
        if (!$propertyFetchVarType instanceof TypeWithClassName) {
            return \false;
        }
        $propertyFetchVarTypeClassName = $propertyFetchVarType->getClassName();
        $classLikeName = $this->nodeNameResolver->getName($class);
        return $propertyFetchVarTypeClassName === $classLikeName;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrPromotedParam
     */
    private function resolvePropertyName($propertyOrPromotedParam) : ?string
    {
        if ($propertyOrPromotedParam instanceof Property) {
            return $this->nodeNameResolver->getName($propertyOrPromotedParam->props[0]);
        }
        return $this->nodeNameResolver->getName($propertyOrPromotedParam->var);
    }
}
