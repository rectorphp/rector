<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector\SimplifyIfElseWithSameContentRectorTest
 */
final class SimplifyIfElseWithSameContentRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove if/else if they have same content', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (true) {
            return 1;
        } else {
            return 1;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 1;
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
     * @return Stmt[]|null
     */
    public function refactor(\PhpParser\Node $node) : ?array
    {
        if ($node->else === null) {
            return null;
        }
        if (!$this->isIfWithConstantReturns($node)) {
            return null;
        }
        return $node->stmts;
    }
    private function isIfWithConstantReturns(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        $possibleContents = [];
        $possibleContents[] = $this->print($if->stmts);
        foreach ($if->elseifs as $elseif) {
            $possibleContents[] = $this->print($elseif->stmts);
        }
        $else = $if->else;
        if (!$else instanceof \PhpParser\Node\Stmt\Else_) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $possibleContents[] = $this->print($else->stmts);
        $uniqueContents = \array_unique($possibleContents);
        // only one content for all
        return \count($uniqueContents) === 1;
    }
}
