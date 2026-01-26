<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class VariableFinder
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return Variable[]
     */
    public function find(Node $node, string $variableName): array
    {
        $variables = $this->betterNodeFinder->findInstancesOfScoped([$node], Variable::class);
        return array_filter($variables, fn(Variable $variable): bool => $this->nodeNameResolver->isName($variable, $variableName));
    }
}
