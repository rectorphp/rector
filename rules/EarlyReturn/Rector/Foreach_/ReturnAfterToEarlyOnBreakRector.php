<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\Foreach_\ReturnAfterToEarlyOnBreakRector\ReturnAfterToEarlyOnBreakRectorTest
 */
final class ReturnAfterToEarlyOnBreakRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change return after foreach to early return in foreach on break', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $pathConstants, string $allowedPath)
    {
        $pathOK = false;

        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                $pathOK = true;
                break;
            }
        }

        return $pathOK;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $pathConstants, string $allowedPath)
    {
        foreach ($pathConstants as $allowedPath) {
            if ($dirPath == $allowedPath) {
                return true;
            }
        }

        return false;
    }
}
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
        /** @var Break_[] $breaks */
        $breaks = $this->betterNodeFinder->findInstanceOf($node->stmts, Break_::class);
        if (\count($breaks) !== 1) {
            return null;
        }
        $beforeBreak = $breaks[0]->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (!$beforeBreak instanceof Expression) {
            return null;
        }
        $assign = $beforeBreak->expr;
        if (!$assign instanceof Assign) {
            return null;
        }
        $nextForeach = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (!$nextForeach instanceof Return_) {
            return null;
        }
        $assignVariable = $assign->var;
        /** @var Expr $variablePrevious */
        $variablePrevious = $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use($assignVariable) : bool {
            $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parent instanceof Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node, $assignVariable);
        });
        if ($this->shouldSkip($nextForeach, $node, $assignVariable, $variablePrevious)) {
            return null;
        }
        /** @var Assign $assignPreviousVariable */
        $assignPreviousVariable = $variablePrevious->getAttribute(AttributeKey::PARENT_NODE);
        $parent = $assignPreviousVariable->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof Expression) {
            return null;
        }
        $nextParent = $parent->getAttribute(AttributeKey::NEXT_NODE);
        if ($nextParent !== $node) {
            return null;
        }
        return $this->processEarlyReturn($beforeBreak, $assign, $breaks, $nextForeach, $assignPreviousVariable, $node);
    }
    /**
     * @param Break_[] $breaks
     */
    private function processEarlyReturn(Expression $expression, Assign $assign, array $breaks, Return_ $return, Assign $assignPreviousVariable, Foreach_ $foreach) : Foreach_
    {
        $this->removeNode($expression);
        $this->nodesToAddCollector->addNodeBeforeNode(new Return_($assign->expr), $breaks[0], $this->file->getSmartFileInfo());
        $this->removeNode($breaks[0]);
        $return->expr = $assignPreviousVariable->expr;
        $this->removeNode($assignPreviousVariable);
        return $foreach;
    }
    private function shouldSkip(Return_ $return, Foreach_ $foreach, Expr $assignVariable, Expr $expr = null) : bool
    {
        if (!$expr instanceof Expr) {
            return \true;
        }
        if (!$this->nodeComparator->areNodesEqual($return->expr, $expr)) {
            return \true;
        }
        // ensure the variable only used once in foreach
        $usedVariable = $this->betterNodeFinder->find($foreach->stmts, function (Node $node) use($assignVariable) : bool {
            return $this->nodeComparator->areNodesEqual($node, $assignVariable);
        });
        return \count($usedVariable) > 1;
    }
}
