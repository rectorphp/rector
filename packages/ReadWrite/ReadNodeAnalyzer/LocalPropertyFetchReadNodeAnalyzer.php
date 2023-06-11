<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\ReadWrite\Contract\ReadNodeAnalyzerInterface;
/**
 * @implements ReadNodeAnalyzerInterface<PropertyFetch|StaticPropertyFetch>
 */
final class LocalPropertyFetchReadNodeAnalyzer implements ReadNodeAnalyzerInterface
{
    /**
     * @var \Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer
     */
    private $justReadExprAnalyzer;
    /**
     * @var \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder
     */
    private $propertyFetchFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\ReadWrite\ReadNodeAnalyzer\JustReadExprAnalyzer $justReadExprAnalyzer, PropertyFetchFinder $propertyFetchFinder, NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->justReadExprAnalyzer = $justReadExprAnalyzer;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function supports(Expr $expr) : bool
    {
        return $expr instanceof PropertyFetch || $expr instanceof StaticPropertyFetch;
    }
    public function isRead(Expr $expr) : bool
    {
        $class = $this->betterNodeFinder->findParentType($expr, Class_::class);
        if (!$class instanceof Class_) {
            // assume worse to keep node protected
            return \true;
        }
        $propertyName = $this->nodeNameResolver->getName($expr->name);
        if ($propertyName === null) {
            // assume worse to keep node protected
            return \true;
        }
        $propertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($class, $propertyName);
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->justReadExprAnalyzer->isReadContext($propertyFetch)) {
                return \true;
            }
        }
        return \false;
    }
}
