<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
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
     * @var string
     */
    private const THIS = 'this';
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
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    /**
     * @return PropertyFetch[]|StaticPropertyFetch[]
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrPromotedParam
     */
    public function findPrivatePropertyFetches(\PhpParser\Node\Stmt\Class_ $class, $propertyOrPromotedParam) : array
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
    public function findLocalPropertyFetchesByName(\PhpParser\Node\Stmt\Class_ $class, string $paramName) : array
    {
        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->findInstancesOf($class, [\PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\StaticPropertyFetch::class]);
        $foundPropertyFetches = [];
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->propertyFetchAnalyzer->isLocalPropertyFetchName($propertyFetch, $paramName)) {
                $foundPropertyFetches[] = $propertyFetch;
            }
        }
        return $foundPropertyFetches;
    }
    /**
     * @param Stmt[] $stmts
     * @return PropertyFetch[]|StaticPropertyFetch[]
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_ $class
     */
    private function findPropertyFetchesInClassLike($class, array $stmts, string $propertyName, bool $hasTrait) : array
    {
        /** @var PropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->findInstanceOf($stmts, \PhpParser\Node\Expr\PropertyFetch::class);
        /** @var PropertyFetch[] $matchingPropertyFetches */
        $matchingPropertyFetches = \array_filter($propertyFetches, function (\PhpParser\Node\Expr\PropertyFetch $propertyFetch) use($propertyName, $class, $hasTrait) : bool {
            if ($this->isInAnonymous($propertyFetch, $class, $hasTrait)) {
                return \false;
            }
            return $this->isNamePropertyNameEquals($propertyFetch, $propertyName, $class);
        });
        /** @var StaticPropertyFetch[] $staticPropertyFetches */
        $staticPropertyFetches = $this->betterNodeFinder->findInstanceOf($stmts, \PhpParser\Node\Expr\StaticPropertyFetch::class);
        /** @var StaticPropertyFetch[] $matchingStaticPropertyFetches */
        $matchingStaticPropertyFetches = \array_filter($staticPropertyFetches, function (\PhpParser\Node\Expr\StaticPropertyFetch $staticPropertyFetch) use($propertyName) : bool {
            return $this->nodeNameResolver->isName($staticPropertyFetch->name, $propertyName);
        });
        return \array_merge($matchingPropertyFetches, $matchingStaticPropertyFetches);
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_ $class
     */
    private function isInAnonymous(\PhpParser\Node\Expr\PropertyFetch $propertyFetch, $class, bool $hasTrait) : bool
    {
        $parent = $this->betterNodeFinder->findParentType($propertyFetch, \PhpParser\Node\Stmt\Class_::class);
        if (!$parent instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        return $parent !== $class && !$hasTrait;
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_ $class
     */
    private function isNamePropertyNameEquals(\PhpParser\Node\Expr\PropertyFetch $propertyFetch, string $propertyName, $class) : bool
    {
        // early check if property fetch name is not equals with property name
        // so next check is check var name and var type only
        if (!$this->nodeNameResolver->isName($propertyFetch->name, $propertyName)) {
            return \false;
        }
        if ($this->nodeNameResolver->isName($propertyFetch->var, self::THIS)) {
            return \true;
        }
        $propertyFetchVarType = $this->nodeTypeResolver->getType($propertyFetch->var);
        if (!$propertyFetchVarType instanceof \PHPStan\Type\TypeWithClassName) {
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
        if ($propertyOrPromotedParam instanceof \PhpParser\Node\Stmt\Property) {
            return $this->nodeNameResolver->getName($propertyOrPromotedParam->props[0]);
        }
        return $this->nodeNameResolver->getName($propertyOrPromotedParam->var);
    }
}
