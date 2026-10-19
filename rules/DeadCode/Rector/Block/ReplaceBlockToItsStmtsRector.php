<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Block;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Block;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Block\ReplaceBlockToItsStmtsRector\ReplaceBlockToItsStmtsRectorTest
 * @see https://3v4l.org/ZUfEV
 */
final class ReplaceBlockToItsStmtsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace Block Stmt with its stmts', [new CodeSample(<<<'CODE_SAMPLE'
{
    echo "statement 1";
    echo PHP_EOL;
    echo "statement 2";
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo "statement 1";
echo PHP_EOL;
echo "statement 2";
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Block::class];
    }
    /**
     * @param Block $node
     * @return int|Stmt[]
     */
    public function refactor(Node $node)
    {
        if ($node->stmts === []) {
            return NodeVisitor::REMOVE_NODE;
        }
        $stmts = $this->flattenBlocks($node->stmts);
        if ($stmts === []) {
            return NodeVisitor::REMOVE_NODE;
        }
        return $stmts;
    }
    /**
     * Flatten nested blocks and drop empty ones in the same pass, as replaced stmts are not re-traversed
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function flattenBlocks(array $stmts): array
    {
        $flattenedStmts = [];
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Block) {
                $flattenedStmts = array_merge($flattenedStmts, $this->flattenBlocks($stmt->stmts));
                continue;
            }
            $flattenedStmts[] = $stmt;
        }
        return $flattenedStmts;
    }
}
