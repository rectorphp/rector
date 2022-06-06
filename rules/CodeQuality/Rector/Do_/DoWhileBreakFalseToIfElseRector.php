<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Do_;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Do_\DoWhileBreakFalseToIfElseRector\DoWhileBreakFalseToIfElseRectorTest
 */
final class DoWhileBreakFalseToIfElseRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace do (...} while (false); with more readable if/else conditions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
do {
    if (mt_rand(0, 1)) {
        $value = 5;
        break;
    }

    $value = 10;
} while (false);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if (mt_rand(0, 1)) {
    $value = 5;
} else {
    $value = 10;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Do_::class];
    }
    /**
     * @param Do_ $node
     * @return mixed[]|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if (!$this->valueResolver->isFalse($node->cond)) {
            return null;
        }
        $currentStmts = $node->stmts;
        return $this->resolveNewStmts($currentStmts);
    }
    /**
     * @param Stmt[] $currentStmts
     * @return Stmt[]
     */
    private function resolveNewStmts(array $currentStmts) : array
    {
        $foundBreak = $this->betterNodeFinder->findFirstInstanceOf($currentStmts, \PhpParser\Node\Stmt\Break_::class);
        if (!$foundBreak instanceof \PhpParser\Node\Stmt\Break_) {
            return $currentStmts;
        }
        $newStmts = [];
        foreach ($currentStmts as $key => $currentStmt) {
            $foundBreak = $this->betterNodeFinder->findFirstInstanceOf($currentStmt, \PhpParser\Node\Stmt\Break_::class);
            if (!$foundBreak instanceof \PhpParser\Node\Stmt\Break_) {
                continue;
            }
            $this->removeNode($foundBreak);
            // collect rest of nodes
            $restOfStmts = \array_slice($currentStmts, $key + 1, \count($currentStmts));
            $currentIf = $currentStmt instanceof \PhpParser\Node\Stmt\If_ ? $currentStmt : $this->betterNodeFinder->findInstanceOf($currentStmt, \PhpParser\Node\Stmt\If_::class);
            if (!$currentIf instanceof \PhpParser\Node\Stmt\If_) {
                continue;
            }
            // reprint new tokens
            $currentIf->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
            if ($restOfStmts !== []) {
                $restOfStmts = $this->resolveNewStmts($restOfStmts);
                $currentIf->else = new \PhpParser\Node\Stmt\Else_($restOfStmts);
            }
            $newStmts[] = $currentStmt;
            break;
        }
        return $newStmts;
    }
}
