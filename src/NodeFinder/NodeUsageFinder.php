<?php

declare(strict_types=1);

namespace Rector\Core\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;

final class NodeUsageFinder
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        BetterNodeFinder $betterNodeFinder,
        NodeRepository $nodeRepository
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @param Node[] $nodes
     * @return Variable[]
     */
    public function findVariableUsages(array $nodes, Variable $variable): array
    {
        $variableName = $this->nodeNameResolver->getName($variable);

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
}
