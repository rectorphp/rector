<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\SimplifyIfIssetToNullCoalescingRector\SimplifyIfIssetToNullCoalescingRectorTest
 */
final class SimplifyIfIssetToNullCoalescingRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify binary if to null coalesce', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run($possibleStatieYamlFile)
    {
        if (isset($possibleStatieYamlFile['import'])) {
            $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'], $filesToImport);
        } else {
            $possibleStatieYamlFile['import'] = $filesToImport;
        }
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run($possibleStatieYamlFile)
    {
        $possibleStatieYamlFile['import'] = array_merge($possibleStatieYamlFile['import'] ?? [], $filesToImport);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var Isset_ $issetNode */
        $issetNode = $node->cond;

        $valueNode = $issetNode->vars[0];

        // various scenarios
        /** @var Assign $firstAssign */
        $firstAssign = $node->stmts[0]->expr;
        /** @var Assign $secondAssign */
        $secondAssign = $node->else->stmts[0]->expr;

        // 1. array_merge
        if (! $firstAssign->expr instanceof FuncCall) {
            return null;
        }

        if (! $this->isName($firstAssign->expr, 'array_merge')) {
            return null;
        }

        if (! $this->areNodesEqual($firstAssign->expr->args[0]->value, $valueNode)) {
            return null;
        }

        if (! $this->areNodesEqual($secondAssign->expr, $firstAssign->expr->args[1]->value)) {
            return null;
        }

        $args = [new Arg(new Coalesce($valueNode, new Array_([]))), new Arg($secondAssign->expr)];
        $funcCall = new FuncCall(new Name('array_merge'), $args);

        return new Assign($valueNode, $funcCall);
    }

    private function shouldSkip(If_ $if): bool
    {
        if ($if->else === null) {
            return true;
        }

        if (count($if->elseifs) > 1) {
            return true;
        }

        if (! $if->cond instanceof Isset_) {
            return true;
        }

        if (! $this->hasOnlyStatementAssign($if)) {
            return true;
        }

        if (! $this->hasOnlyStatementAssign($if->else)) {
            return true;
        }

        if (! $this->areNodesEqual($if->cond->vars[0], $if->stmts[0]->expr->var)) {
            return true;
        }
        return ! $this->areNodesEqual($if->cond->vars[0], $if->else->stmts[0]->expr->var);
    }

    /**
     * @param If_|Else_ $node
     */
    private function hasOnlyStatementAssign(Node $node): bool
    {
        if (count($node->stmts) !== 1) {
            return false;
        }

        if (! $node->stmts[0] instanceof Expression) {
            return false;
        }

        return $node->stmts[0]->expr instanceof Assign;
    }
}
