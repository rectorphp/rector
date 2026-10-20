<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\RemoveAlwaysElseRector\RemoveAlwaysElseRectorTest
 */
final class RemoveAlwaysElseRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Split if statement, when if condition always break execution flow', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        } else {
            return 10;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        }

        return 10;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return Node[]|null
     */
    public function refactor(Node $node): ?array
    {
        $nodesToReturn = $this->splitAlwaysElse($node);
        if ($nodesToReturn === null) {
            return null;
        }
        // the returned stmts are not re-traversed in this pass, so split nested always-else here too
        return $this->splitNestedAlwaysElse($nodesToReturn);
    }
    /**
     * @return Node[]|null
     */
    private function splitAlwaysElse(If_ $node): ?array
    {
        if ($this->doesNotLastStatementBreakFlow($node)) {
            return null;
        }
        if ($node->elseifs !== []) {
            return $this->handleElseIfs($node);
        }
        if ($node->else instanceof Else_) {
            $stmts = $node->else->stmts;
            $node->else = null;
            return array_merge([$node], $stmts);
        }
        return null;
    }
    /**
     * @param Node[] $stmts
     * @return Node[]
     */
    private function splitNestedAlwaysElse(array $stmts): array
    {
        $splitStmts = [];
        foreach ($stmts as $stmt) {
            $this->splitAlwaysElseInSubStmts($stmt);
            if ($stmt instanceof If_) {
                $nestedNodesToReturn = $this->splitAlwaysElse($stmt);
                if ($nestedNodesToReturn !== null) {
                    $splitStmts = array_merge($splitStmts, $this->splitNestedAlwaysElse($nestedNodesToReturn));
                    continue;
                }
            }
            $splitStmts[] = $stmt;
        }
        return $splitStmts;
    }
    private function splitAlwaysElseInSubStmts(Node $node): void
    {
        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->{$subNodeName};
            if (!is_array($subNode) || $subNode === []) {
                continue;
            }
            // only statement lists are split; expression/argument lists are left untouched
            foreach ($subNode as $subNodeItem) {
                if (!$subNodeItem instanceof Stmt) {
                    continue 2;
                }
            }
            $node->{$subNodeName} = $this->splitNestedAlwaysElse($subNode);
        }
    }
    /**
     * @return Node[]
     */
    private function handleElseIfs(If_ $if): array
    {
        $nodesToReturn = [];
        $originalIf = clone $if;
        $firstIf = $this->createIfFromNode($if);
        $nodesToReturn[] = $firstIf;
        while ($if->elseifs !== []) {
            /** @var ElseIf_ $currentElseIf */
            $currentElseIf = array_shift($if->elseifs);
            // If the last statement in the `elseif` breaks flow, merge it into the original `if` and stop processing
            if ($this->doesNotLastStatementBreakFlow($currentElseIf)) {
                $this->updateIfWithElseIf($if, $currentElseIf);
                $nodesToReturn = array_merge($nodesToReturn, [$if], $this->getStatementsElseIfs($if));
                break;
            }
            $isLastElseIf = $if->elseifs === [];
            // If it's the last `elseif`, merge it with the original `if` to keep the formatting
            if ($isLastElseIf) {
                $this->updateIfWithElseIf($if, $currentElseIf);
                $nodesToReturn[] = $if;
                break;
            }
            // Otherwise, create a separate `if` node for `elseif`
            $nodesToReturn[] = $this->createIfFromNode($currentElseIf);
        }
        if ($originalIf->else instanceof Else_) {
            $mergeStmts = \true;
            foreach ($nodesToReturn as $nodeToReturn) {
                if ($this->doesNotLastStatementBreakFlow($nodeToReturn)) {
                    $nodesToReturn[] = $originalIf->else;
                    $mergeStmts = \false;
                    break;
                }
            }
            if ($mergeStmts) {
                $nodesToReturn = array_merge($nodesToReturn, $originalIf->else->stmts);
            }
        }
        return $nodesToReturn;
    }
    /**
     * @return ElseIf_[]
     */
    private function getStatementsElseIfs(If_ $if): array
    {
        $statements = [];
        foreach ($if->elseifs as $key => $elseif) {
            if ($this->doesNotLastStatementBreakFlow($elseif) && $elseif->stmts !== []) {
                continue;
            }
            $statements[] = $elseif;
            unset($if->elseifs[$key]);
        }
        return $statements;
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\ElseIf_|\PhpParser\Node\Stmt\Else_ $node
     */
    private function doesNotLastStatementBreakFlow($node): bool
    {
        $lastStmt = end($node->stmts);
        if ($lastStmt instanceof If_ && $lastStmt->else instanceof Else_) {
            if ($this->doesNotLastStatementBreakFlow($lastStmt) || $this->doesNotLastStatementBreakFlow($lastStmt->else)) {
                return \true;
            }
            $found = \false;
            foreach ($lastStmt->elseifs as $elseIf) {
                if ($this->doesNotLastStatementBreakFlow($elseIf)) {
                    $found = \true;
                    break;
                }
            }
            return $found;
        }
        return !($lastStmt instanceof Return_ || $lastStmt instanceof Expression && $lastStmt->expr instanceof Throw_ || $lastStmt instanceof Continue_ || $lastStmt instanceof Expression && $lastStmt->expr instanceof Exit_);
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\ElseIf_ $node
     */
    private function createIfFromNode($node): If_
    {
        $if = new If_($node->cond);
        $if->stmts = $node->stmts;
        $this->mirrorComments($if, $node);
        return $if;
    }
    private function updateIfWithElseIf(If_ $if, ElseIf_ $elseIf): void
    {
        $if->cond = $elseIf->cond;
        $if->stmts = $elseIf->stmts;
        $this->mirrorComments($if, $elseIf);
        $if->else = null;
    }
}
