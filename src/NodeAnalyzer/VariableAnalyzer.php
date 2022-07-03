<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
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
        if ($this->isParentStaticOrGlobal($variable)) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirstPrevious($variable, function (Node $node) use($variable) : bool {
            if (!\in_array(\get_class($node), [Static_::class, Global_::class], \true)) {
                return \false;
            }
            /**
             * @var Static_|Global_ $node
             * @var StaticVar[]|Variable[] $vars
             */
            $vars = $node->vars;
            foreach ($vars as $var) {
                $staticVarVariable = $var instanceof StaticVar ? $var->var : $var;
                if ($this->nodeComparator->areNodesEqual($staticVarVariable, $variable)) {
                    return \true;
                }
            }
            return \false;
        });
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
            $parent = $subNode->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parent instanceof ClosureUse) {
                return \false;
            }
            return $parent->byRef;
        });
    }
    private function isParentStaticOrGlobal(Variable $variable) : bool
    {
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            return \false;
        }
        if ($parentNode instanceof Global_) {
            return \true;
        }
        if (!$parentNode instanceof StaticVar) {
            return \false;
        }
        $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        return $parentParentNode instanceof Static_;
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
