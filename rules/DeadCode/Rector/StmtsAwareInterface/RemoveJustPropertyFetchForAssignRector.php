<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\JustPropertyFetchVariableAssignMatcher;
use Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector\RemoveJustPropertyFetchForAssignRectorTest
 */
final class RemoveJustPropertyFetchForAssignRector extends AbstractRector
{
    public function __construct(
        private readonly JustPropertyFetchVariableAssignMatcher $justPropertyFetchVariableAssignMatcher
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove assign of property, just for value assign', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $items = [];

    public function run()
    {
        $items = $this->items;
        $items[] = 1000;
        $this->items = $items ;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $items = [];

    public function run()
    {
        $this->items[] = 1000;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class];
    }

    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        $variableAndPropertyFetchAssign = $this->justPropertyFetchVariableAssignMatcher->match($node);
        if (! $variableAndPropertyFetchAssign instanceof VariableAndPropertyFetchAssign) {
            return null;
        }

        $secondStmt = $node->stmts[1];
        if (! $secondStmt instanceof Expression) {
            return null;
        }

        if (! $secondStmt->expr instanceof Assign) {
            return null;
        }

        $middleAssign = $secondStmt->expr;

        if ($middleAssign->var instanceof Variable) {
            return $this->refactorToVariableAssign($middleAssign, $variableAndPropertyFetchAssign, $node);
        }

        if ($middleAssign->var instanceof ArrayDimFetch) {
            return $this->removeToArrayDimFetchAssign($middleAssign, $variableAndPropertyFetchAssign, $node);
        }

        return null;
    }

    private function refactorToVariableAssign(
        Assign $middleAssign,
        VariableAndPropertyFetchAssign $variableAndPropertyFetchAssign,
        StmtsAwareInterface $stmtsAware
    ): StmtsAwareInterface|Node|null {
        $middleVariable = $middleAssign->var;

        if (! $this->nodeComparator->areNodesEqual($middleVariable, $variableAndPropertyFetchAssign->getVariable())) {
            return null;
        }

        // remove just-assign stmts
        unset($stmtsAware->stmts[0]);
        unset($stmtsAware->stmts[2]);

        $middleAssign->var = $variableAndPropertyFetchAssign->getPropertyFetch();

        return $stmtsAware;
    }

    private function removeToArrayDimFetchAssign(
        Assign $middleAssign,
        VariableAndPropertyFetchAssign $variableAndPropertyFetchAssign,
        StmtsAwareInterface $stmtsAware
    ): StmtsAwareInterface|null {
        $middleArrayDimFetch = $middleAssign->var;
        if (! $middleArrayDimFetch instanceof ArrayDimFetch) {
            return null;
        }

        if ($middleArrayDimFetch->var instanceof Variable) {
            $middleNestedVariable = $middleArrayDimFetch->var;
            if (! $this->nodeComparator->areNodesEqual(
                $middleNestedVariable,
                $variableAndPropertyFetchAssign->getVariable()
            )) {
                return null;
            }

            $middleArrayDimFetch->var = $variableAndPropertyFetchAssign->getPropertyFetch();
        }

        // remove just-assign stmts
        unset($stmtsAware->stmts[0]);
        unset($stmtsAware->stmts[2]);

        return $stmtsAware;
    }
}
