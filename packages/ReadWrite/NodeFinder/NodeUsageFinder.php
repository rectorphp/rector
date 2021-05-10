<?php

declare(strict_types=1);

namespace Rector\ReadWrite\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder;

final class NodeUsageFinder
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private BetterNodeFinder $betterNodeFinder,
        private NodeRepository $nodeRepository,
        private ScopeAwareNodeFinder $scopeAwareNodeFinder,
        private NodeComparator $nodeComparator
    ) {
    }

    /**
     * @param Node[] $nodes
     * @return Variable[]
     */
    public function findVariableUsages(array $nodes, Variable $variable): array
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        if ($variableName === null) {
            return [];
        }

        return $this->betterNodeFinder->find($nodes, function (Node $node) use ($variable, $variableName): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            if ($node === $variable) {
                return false;
            }

            return $this->nodeNameResolver->isName($node, $variableName);
        });
    }

    /**
     * @return PropertyFetch[]
     */
    public function findPropertyFetchUsages(PropertyFetch $desiredPropertyFetch): array
    {
        $propertyFetches = $this->nodeRepository->findPropertyFetchesByPropertyFetch($desiredPropertyFetch);

        $propertyFetchesWithoutPropertyFetch = [];
        foreach ($propertyFetches as $propertyFetch) {
            if ($propertyFetch === $desiredPropertyFetch) {
                continue;
            }

            $propertyFetchesWithoutPropertyFetch[] = $propertyFetch;
        }

        return $propertyFetchesWithoutPropertyFetch;
    }

    public function findPreviousForeachNodeUsage(Foreach_ $foreach, Expr $expr): ?Node
    {
        return $this->scopeAwareNodeFinder->findParent($foreach, function (Node $node) use ($expr): bool {
            // skip itself
            if ($node === $expr) {
                return false;
            }

            return $this->nodeComparator->areNodesEqual($node, $expr);
        }, [Foreach_::class]);
    }
}
