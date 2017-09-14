<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;

/**
 * Detects node that have dynamic value and cannot be determined, e.g. variable, property.
 */
final class DynamicNodeAnalyzer
{
    /**
     * @param Node[] $nodes
     */
    public function hasDynamicNodes(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if ($this->isDynamicNode($node)) {
                return true;
            }
        }

        return false;
    }

    public function isDynamicNode(Node $node): bool
    {
        $nodeClass = get_class($node);

        return in_array($nodeClass, [PropertyFetch::class, MethodCall::class, Variable::class], true);
    }
}
