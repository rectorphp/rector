<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VariableUseFinder
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        NodeNameResolver $nodeNameResolver,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @param Variable[] $assignedVariables
     * @return Variable[]
     */
    public function resolveUsedVariables(Node $node, array $assignedVariables): array
    {
        return $this->betterNodeFinder->find($node, function (Node $node) use ($assignedVariables): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            // is the left assign - not use of one
            if ($parentNode instanceof Assign && ($parentNode->var instanceof Variable && $parentNode->var === $node)) {
                return false;
            }
            $nodeNameResolverGetName = $this->nodeNameResolver->getName($node);

            // simple variable only
            if ($nodeNameResolverGetName === null) {
                return false;
            }

            return $this->betterStandardPrinter->isNodeEqual($node, $assignedVariables);
        });
    }
}
