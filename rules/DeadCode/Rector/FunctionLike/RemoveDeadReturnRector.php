<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector\RemoveDeadReturnRectorTest
 */
final class RemoveDeadReturnRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove last return in the functions, since does not do anything', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return;
        }

        return;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return;
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === [] || $node->stmts === null) {
            return null;
        }
        \end($node->stmts);
        $lastStmtKey = \key($node->stmts);
        $lastStmt = $node->stmts[$lastStmtKey];
        if ($lastStmt instanceof If_) {
            if (!$this->isBareIfWithOnlyStmtEmptyReturn($lastStmt)) {
                return null;
            }
            $lastStmt->stmts = [];
            return $node;
        }
        if (!$lastStmt instanceof Return_) {
            return null;
        }
        if ($lastStmt->expr instanceof Expr) {
            return null;
        }
        unset($node->stmts[$lastStmtKey]);
        return $node;
    }
    private function isBareIfWithOnlyStmtEmptyReturn(If_ $if) : bool
    {
        if ($if->else instanceof Else_) {
            return \false;
        }
        if ($if->elseifs !== []) {
            return \false;
        }
        if (\count($if->stmts) !== 1) {
            return \false;
        }
        $onlyStmt = $if->stmts[0];
        if (!$onlyStmt instanceof Return_) {
            return \false;
        }
        return !$onlyStmt->expr instanceof Expr;
    }
}
