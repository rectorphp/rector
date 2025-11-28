<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Nop;
use Rector\Comments\CommentResolver;
use Rector\Contract\Rector\HTMLAverseRectorInterface;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\NewlineAfterStatementRectorTest
 */
final class NewlineAfterStatementRector extends AbstractRector implements HTMLAverseRectorInterface
{
    /**
     * @readonly
     */
    private CommentResolver $commentResolver;
    public function __construct(CommentResolver $commentResolver)
    {
        $this->commentResolver = $commentResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add empty new line after different-type statements to improve code readability', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($input)
    {
        $value = 5 * $input;
        $secondValue = $value ^ 5;
        return $value - $secondValue;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($input)
    {
        $value = 5 * $input;

        $secondValue = $value ^ 5;

        return $value - $secondValue;
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
     * @return StmtsAware|null
     */
    public function refactor(Node $node): ?\PhpParser\Node
    {
        return $this->processAddNewLine($node, \false);
    }
    /**
     * @param StmtsAware $node
     * @return StmtsAware|null
     */
    private function processAddNewLine(Node $node, bool $hasChanged, int $jumpToKey = 0): ?\PhpParser\Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $totalKeys = array_key_last($node->stmts);
        for ($key = $jumpToKey; $key < $totalKeys; ++$key) {
            if (!isset($node->stmts[$key], $node->stmts[$key + 1])) {
                break;
            }
            $stmt = $node->stmts[$key];
            $nextStmt = $node->stmts[$key + 1];
            if ($this->shouldSkip($stmt)) {
                continue;
            }
            /** @var Stmt $stmt */
            $endLine = $stmt->getEndLine();
            $rangeLine = $nextStmt->getStartLine() - $endLine;
            if ($rangeLine > 1) {
                $rangeLine = $this->commentResolver->resolveRangeLineFromComment($rangeLine, $endLine, $nextStmt);
            }
            // skip same line or < 0 that cause infinite loop or crash
            if ($rangeLine !== 1) {
                continue;
            }
            array_splice($node->stmts, $key + 1, 0, [new Nop()]);
            $hasChanged = \true;
            return $this->processAddNewLine($node, $hasChanged, $key + 2);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkip(Stmt $stmt): bool
    {
        return !in_array(get_class($stmt), NodeGroup::STMTS_TO_HAVE_NEXT_NEWLINE, \true);
    }
}
