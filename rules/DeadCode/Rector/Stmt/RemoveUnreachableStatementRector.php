<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeAnalyzer\TerminatedNodeAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\RemoveUnreachableStatementRectorTest
 */
final class RemoveUnreachableStatementRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\TerminatedNodeAnalyzer
     */
    private $terminatedNodeAnalyzer;
    public function __construct(TerminatedNodeAnalyzer $terminatedNodeAnalyzer)
    {
        $this->terminatedNodeAnalyzer = $terminatedNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unreachable statements', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5;

        $removeMe = 10;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5;
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $originalStmts = $node->stmts;
        $cleanedStmts = $this->processCleanUpUnreachabelStmts($node->stmts);
        if ($cleanedStmts === $originalStmts) {
            return null;
        }
        $node->stmts = $cleanedStmts;
        return $node;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function processCleanUpUnreachabelStmts(array $stmts) : array
    {
        foreach ($stmts as $key => $stmt) {
            if (!isset($stmts[$key - 1])) {
                continue;
            }
            $previousStmt = $stmts[$key - 1];
            // unset...
            if ($this->terminatedNodeAnalyzer->isAlwaysTerminated($previousStmt, $stmt)) {
                \array_splice($stmts, $key);
                return $stmts;
            }
        }
        return $stmts;
    }
}
