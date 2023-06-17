<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\ClassLikeAstResolver;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
/**
 * @implements ReadNodeAnalyzerInterface<PropertyFetch|StaticPropertyFetch>
 */
final class LocalPropertyFetchReadNodeAnalyzer implements ReadNodeAnalyzerInterface
{
    /**
     * @readonly
     * @var \Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer
     */
    private $justReadExprAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder
     */
    private $propertyFetchFinder;
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
     * @var \Rector\Core\PhpParser\ClassLikeAstResolver
     */
    private $classLikeAstResolver;
    public function __construct(\Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer $justReadExprAnalyzer, PropertyFetchFinder $propertyFetchFinder, NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver, ClassLikeAstResolver $classLikeAstResolver)
    {
        $this->justReadExprAnalyzer = $justReadExprAnalyzer;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->classLikeAstResolver = $classLikeAstResolver;
    }
    public function supports(Expr $expr) : bool
    {
        return $expr instanceof PropertyFetch || $expr instanceof StaticPropertyFetch;
    }
    public function isRead(Expr $expr) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($expr);
        if (!$classReflection instanceof ClassReflection || !$classReflection->isClass()) {
            // assume worse to keep node protected
            return \true;
        }
        $propertyName = $this->nodeNameResolver->getName($expr->name);
        if ($propertyName === null) {
            // assume worse to keep node protected
            return \true;
        }
        /** @var Class_ $class */
        $class = $this->classLikeAstResolver->resolveClassFromClassReflection($classReflection);
        $propertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($class, $propertyName);
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->justReadExprAnalyzer->isReadContext($propertyFetch)) {
                return \true;
            }
        }
        return \false;
    }
}
