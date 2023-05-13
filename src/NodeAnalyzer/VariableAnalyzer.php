<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class VariableAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    public function isStaticOrGlobal(Variable $variable) : bool
    {
        if ($variable->getAttribute(AttributeKey::IS_GLOBAL_VAR) === \true) {
            return \true;
        }
        return $variable->getAttribute(AttributeKey::IS_STATIC_VAR) === \true;
    }
    public function isUsedByReference(Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($variable, function (Node $subNode) use($variable) : bool {
            if ($this->isParamReferenced($subNode, $variable)) {
                return \true;
            }
            if (!$subNode instanceof Variable) {
                return \false;
            }
            if (!$this->nodeComparator->areNodesEqual($subNode, $variable)) {
                return \false;
            }
            $parentNode = $subNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof ClosureUse) {
                return $parentNode->byRef;
            }
            return $parentNode instanceof AssignRef;
        });
    }
    private function isParamReferenced(Node $node, Variable $variable) : bool
    {
        if (!$node instanceof Param) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($node->var, $variable)) {
            return \false;
        }
        return $node->byRef;
    }
}
