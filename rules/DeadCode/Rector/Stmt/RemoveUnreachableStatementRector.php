<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\NodeAnalyzer\TerminatedNodeAnalyzer;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\RemoveUnreachableStatementRectorTest
 */
final class RemoveUnreachableStatementRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TerminatedNodeAnalyzer $terminatedNodeAnalyzer;
    public function __construct(TerminatedNodeAnalyzer $terminatedNodeAnalyzer)
    {
        $this->terminatedNodeAnalyzer = $terminatedNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof FileNode && $node->isNamespaced()) {
            // handled in Namespace_ node
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        // at least 2 items are needed
        if (count($node->stmts) < 2) {
            return null;
        }
        $originalStmts = $node->stmts;
        $cleanedStmts = $this->processCleanUpUnreachableStmts($node, $node->stmts);
        if ($cleanedStmts === $originalStmts) {
            return null;
        }
        $node->stmts = $cleanedStmts;
        return $node;
    }
    /**
     * @param StmtsAware $stmtsAware
     *
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function processCleanUpUnreachableStmts(Node $stmtsAware, array $stmts): array
    {
        foreach ($stmts as $key => $stmt) {
            if (!isset($stmts[$key - 1])) {
                continue;
            }
            $previousStmt = $stmts[$key - 1];
            // unset...
            if ($this->terminatedNodeAnalyzer->isAlwaysTerminated($stmtsAware, $previousStmt, $stmt)) {
                array_splice($stmts, $key);
                return $stmts;
            }
        }
        return $stmts;
    }
}
