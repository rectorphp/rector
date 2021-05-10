<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\ShortenElseIfRector\ShortenElseIfRectorTest
 */
final class ShortenElseIfRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Shortens else/if to elseif', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($cond1) {
            return $action1;
        } else {
            if ($cond2) {
                return $action2;
            }
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($cond1) {
            return $action1;
        } elseif ($cond2) {
            return $action2;
        }
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        return $this->shortenElseIf($node);
    }
    private function shortenElseIf(\PhpParser\Node\Stmt\If_ $node) : ?\PhpParser\Node\Stmt\If_
    {
        if (!$node->else instanceof \PhpParser\Node\Stmt\Else_) {
            return null;
        }
        $else = $node->else;
        if (\count($else->stmts) !== 1) {
            return null;
        }
        $if = $else->stmts[0];
        if (!$if instanceof \PhpParser\Node\Stmt\If_) {
            return null;
        }
        // Try to shorten the nested if before transforming it to elseif
        $refactored = $this->shortenElseIf($if);
        if ($refactored !== null) {
            $if = $refactored;
        }
        $node->elseifs[] = new \PhpParser\Node\Stmt\ElseIf_($if->cond, $if->stmts);
        $node->else = $if->else;
        $node->elseifs = \array_merge($node->elseifs, $if->elseifs);
        return $node;
    }
}
