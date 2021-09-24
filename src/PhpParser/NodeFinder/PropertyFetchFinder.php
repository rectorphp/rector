<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ReflectionProvider;
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
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $propertyOrPromotedParam
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
            /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
            $propertyFetches = $this->findPropertyFetchesInClassLike($classLike->stmts, $propertyName);
            return $propertyFetches;
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
            if ($propertyFetch instanceof \PhpParser\Node\Expr\StaticPropertyFetch && !$this->nodeNameResolver->isName($propertyFetch->class, 'self')) {
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
     * @param Stmt[] $nodes
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    private function findPropertyFetchesInNonAnonymousClassLike(array $nodes, string $propertyName) : array
    {
        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->findPropertyFetchesInClassLike($nodes, $propertyName);
        foreach ($propertyFetches as $key => $propertyFetch) {
            $currentClassLike = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
            if ($this->classAnalyzer->isAnonymousClass($currentClassLike)) {
                unset($propertyFetches[$key]);
            }
        }
        return $propertyFetches;
    }
    /**
     * @param Stmt[] $nodes
     * @return PropertyFetch[]|StaticPropertyFetch[]
     */
    private function findPropertyFetchesInClassLike(array $nodes, string $propertyName) : array
    {
        /** @var PropertyFetch[]|StaticPropertyFetch[] $propertyFetches */
        $propertyFetches = $this->betterNodeFinder->find($nodes, function (\PhpParser\Node $node) use($propertyName) : bool {
            // property + static fetch
            if ($node instanceof \PhpParser\Node\Expr\PropertyFetch && $this->nodeNameResolver->isName($node->var, self::THIS)) {
                return $this->nodeNameResolver->isName($node, $propertyName);
            }
            if (!$node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                return \false;
            }
            if (!$this->nodeNameResolver->isNames($node->class, ['self', self::THIS, 'static'])) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $propertyName);
        });
        return $propertyFetches;
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
