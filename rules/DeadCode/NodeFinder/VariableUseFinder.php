<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class VariableUseFinder
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param Variable[] $assignedVariables
     * @return Variable[]
     */
    public function resolveUsedVariables(\PhpParser\Node $node, array $assignedVariables) : array
    {
        return $this->betterNodeFinder->find($node, function (\PhpParser\Node $node) use($assignedVariables) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            // is the left assign - not use of one
            if ($parentNode instanceof \PhpParser\Node\Expr\Assign && ($parentNode->var instanceof \PhpParser\Node\Expr\Variable && $parentNode->var === $node)) {
                return \false;
            }
            $nodeNameResolverGetName = $this->nodeNameResolver->getName($node);
            // simple variable only
            if ($nodeNameResolverGetName === null) {
                return \false;
            }
            return $this->nodeComparator->isNodeEqual($node, $assignedVariables);
        });
    }
}
