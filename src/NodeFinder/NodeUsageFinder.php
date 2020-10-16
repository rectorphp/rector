<?php

declare(strict_types=1);

namespace Rector\Core\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
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

    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
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
}
