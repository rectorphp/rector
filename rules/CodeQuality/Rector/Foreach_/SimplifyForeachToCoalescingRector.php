<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\ForeachManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/bfsdY
 *
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector\SimplifyForeachToCoalescingRectorTest
 */
final class SimplifyForeachToCoalescingRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var \PhpParser\Node\Stmt\Return_|null
     */
    private $return;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ForeachManipulator
     */
    private $foreachManipulator;
    public function __construct(ForeachManipulator $foreachManipulator)
    {
        $this->foreachManipulator = $foreachManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes foreach that returns set value to ??', [new CodeSample(<<<'CODE_SAMPLE'
foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
    if ($currentFunction === $oldFunction) {
        return $newFunction;
    }
}

return null;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return $this->oldToNewFunctions[$currentFunction] ?? null;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->return = null;
        if ($node->keyVar === null) {
            return null;
        }
        /** @var Return_|Assign|null $returnOrAssignNode */
        $returnOrAssignNode = $this->matchReturnOrAssignNode($node);
        if ($returnOrAssignNode === null) {
            return null;
        }
        // return $newValue;
        // we don't return the node value
        if (!$this->nodeComparator->areNodesEqual($node->valueVar, $returnOrAssignNode->expr)) {
            return null;
        }
        if ($returnOrAssignNode instanceof Return_) {
            return $this->processForeachNodeWithReturnInside($node, $returnOrAssignNode);
        }
        return $this->processForeachNodeWithAssignInside($node, $returnOrAssignNode);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULL_COALESCE;
    }
    /**
     * @return Assign|Return_|null
     */
    private function matchReturnOrAssignNode(Foreach_ $foreach) : ?Node
    {
        return $this->foreachManipulator->matchOnlyStmt($foreach, static function (Node $node) : ?Node {
            if (!$node instanceof If_) {
                return null;
            }
            if (!$node->cond instanceof Identical) {
                return null;
            }
            if (\count($node->stmts) !== 1) {
                return null;
            }
            $innerNode = $node->stmts[0] instanceof Expression ? $node->stmts[0]->expr : $node->stmts[0];
            if ($innerNode instanceof Assign || $innerNode instanceof Return_) {
                return $innerNode;
            }
            return null;
        });
    }
    /**
     * @return \PhpParser\Node\Stmt\Return_|null
     */
    private function processForeachNodeWithReturnInside(Foreach_ $foreach, Return_ $return)
    {
        if (!$this->nodeComparator->areNodesEqual($foreach->valueVar, $return->expr)) {
            return null;
        }
        /** @var If_ $ifNode */
        $ifNode = $foreach->stmts[0];
        /** @var Identical $identicalNode */
        $identicalNode = $ifNode->cond;
        if ($this->nodeComparator->areNodesEqual($identicalNode->left, $foreach->keyVar)) {
            $checkedNode = $identicalNode->right;
        } elseif ($this->nodeComparator->areNodesEqual($identicalNode->right, $foreach->keyVar)) {
            $checkedNode = $identicalNode->left;
        } else {
            return null;
        }
        $nextNode = $foreach->getAttribute(AttributeKey::NEXT_NODE);
        // is next node Return?
        if ($nextNode instanceof Return_) {
            $this->return = $nextNode;
            $this->removeNode($this->return);
        }
        $coalesce = new Coalesce(new ArrayDimFetch($foreach->expr, $checkedNode), $this->return instanceof Return_ && $this->return->expr !== null ? $this->return->expr : $checkedNode);
        if ($this->return !== null) {
            return new Return_($coalesce);
        }
        return null;
    }
    private function processForeachNodeWithAssignInside(Foreach_ $foreach, Assign $assign) : ?Node
    {
        /** @var If_ $ifNode */
        $ifNode = $foreach->stmts[0];
        /** @var Identical $identicalNode */
        $identicalNode = $ifNode->cond;
        if ($this->nodeComparator->areNodesEqual($identicalNode->left, $foreach->keyVar)) {
            $checkedNode = $assign->var;
            $keyNode = $identicalNode->right;
        } elseif ($this->nodeComparator->areNodesEqual($identicalNode->right, $foreach->keyVar)) {
            $checkedNode = $assign->var;
            $keyNode = $identicalNode->left;
        } else {
            return null;
        }
        $arrayDimFetch = new ArrayDimFetch($foreach->expr, $keyNode);
        return new Assign($checkedNode, new Coalesce($arrayDimFetch, $checkedNode));
    }
}
