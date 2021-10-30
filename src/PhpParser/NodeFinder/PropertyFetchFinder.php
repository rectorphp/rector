<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class PropertyFetchFinder
{
    /**
     * @var string
     */
    private const THIS = 'this';
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->astResolver = $astResolver;
        $this->classAnalyzer = $classAnalyzer;
    }
    /**
     * @return PropertyFetch[]|StaticPropertyFetch[]
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\Property $propertyOrPromotedParam
     */
    public function findPrivatePropertyFetches($propertyOrPromotedParam) : array
    {
        $classLike = $propertyOrPromotedParam->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return [];
        }
        $propertyName = $this->resolvePropertyName($propertyOrPromotedParam);
        if ($propertyName === null) {
            return [];
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        if (!$this->reflectionProvider->hasClass($className)) {
            return $this->findPropertyFetchesInClassLike($classLike->stmts, $propertyName);
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $nodes = [$classLike];
        $nodes = \array_merge($nodes, $this->astResolver->parseClassReflectionTraits($classReflection));
        return $this->findPropertyFetchesInNonAnonymousClassLike($nodes, $propertyName);
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
            if ($propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch && !$this->nodeNameResolver->isName($propertyFetch->var, self::THIS)) {
                continue;
            }
            if ($propertyFetch instanceof \PhpParser\Node\Expr\StaticPropertyFetch && !$this->nodeNameResolver->isName($propertyFetch->class, \Rector\Core\Enum\ObjectReference::SELF()->getValue())) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($propertyFetch->name, $paramName)) {
                continue;
            }
            $foundPropertyFetches[] = $propertyFetch;
        }
        return $foundPropertyFetches;
    }
    /**
     * @param Stmt[] $stmts
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    private function findPropertyFetchesInNonAnonymousClassLike(array $stmts, string $propertyName) : array
    {
        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->findPropertyFetchesInClassLike($stmts, $propertyName);
        foreach ($propertyFetches as $key => $propertyFetch) {
            $currentClassLike = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
            if (!$currentClassLike instanceof \PhpParser\Node\Stmt\ClassLike) {
                continue;
            }
            if ($this->classAnalyzer->isAnonymousClass($currentClassLike)) {
                unset($propertyFetches[$key]);
            }
        }
        return $propertyFetches;
    }
    /**
     * @param Stmt[] $stmts
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    private function findPropertyFetchesInClassLike(array $stmts, string $propertyName) : array
    {
        /** @var PropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->findInstanceOf($stmts, \PhpParser\Node\Expr\PropertyFetch::class);
        /** @var PropertyFetch[] $matchingPropertyFetches */
        $matchingPropertyFetches = \array_filter($propertyFetches, function (\PhpParser\Node\Expr\PropertyFetch $propertyFetch) use($propertyName) : bool {
            if (!$this->nodeNameResolver->isName($propertyFetch->var, self::THIS)) {
                return \false;
            }
            return $this->nodeNameResolver->isName($propertyFetch->name, $propertyName);
        });
        /** @var StaticPropertyFetch[] $staticPropertyFetches */
        $staticPropertyFetches = $this->betterNodeFinder->findInstanceOf($stmts, \PhpParser\Node\Expr\StaticPropertyFetch::class);
        /** @var StaticPropertyFetch[] $matchingStaticPropertyFetches */
        $matchingStaticPropertyFetches = \array_filter($staticPropertyFetches, function (\PhpParser\Node\Expr\StaticPropertyFetch $staticPropertyFetch) use($propertyName) : bool {
            return $this->isLocalStaticPropertyByFetchName($staticPropertyFetch, $propertyName);
        });
        return \array_merge($matchingPropertyFetches, $matchingStaticPropertyFetches);
    }
    /**
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\Property $propertyOrPromotedParam
     */
    private function resolvePropertyName($propertyOrPromotedParam) : ?string
    {
        if ($propertyOrPromotedParam instanceof \PhpParser\Node\Stmt\Property) {
            return $this->nodeNameResolver->getName($propertyOrPromotedParam->props[0]);
        }
        return $this->nodeNameResolver->getName($propertyOrPromotedParam->var);
    }
    private function isLocalStaticPropertyByFetchName(\PhpParser\Node\Expr\StaticPropertyFetch $staticPropertyFetch, string $propertyName) : bool
    {
        $class = $this->nodeNameResolver->getName($staticPropertyFetch->class);
        if (!\in_array($class, [\Rector\Core\Enum\ObjectReference::SELF()->getValue(), \Rector\Core\Enum\ObjectReference::STATIC()->getValue(), self::THIS], \true)) {
            return \false;
        }
        return $this->nodeNameResolver->isName($staticPropertyFetch->name, $propertyName);
    }
}
