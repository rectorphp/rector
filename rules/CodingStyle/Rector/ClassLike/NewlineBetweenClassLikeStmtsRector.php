<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\TraitUse;
use Rector\Comments\CommentResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector\NewlineBetweenClassLikeStmtsRectorTest
 */
final class NewlineBetweenClassLikeStmtsRector extends AbstractRector
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
        return new RuleDefinition('Add new line space between class constants, properties and class methods to make it more readable', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public const NAME = 'name';
    public function first()
    {
    }
    public function second()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public const NAME = 'name';

    public function first()
    {
    }

    public function second()
    {
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
        return [ClassLike::class];
    }
    /**
     * @param ClassLike $node
     */
    public function refactor(Node $node): ?ClassLike
    {
        return $this->processAddNewLine($node, \false);
    }
    private function processAddNewLine(ClassLike $classLike, bool $hasChanged, int $jumpToKey = 0): ?\PhpParser\Node\Stmt\ClassLike
    {
        $totalKeys = array_key_last($classLike->stmts);
        for ($key = $jumpToKey; $key < $totalKeys; ++$key) {
            if (!isset($classLike->stmts[$key], $classLike->stmts[$key + 1])) {
                break;
            }
            $stmt = $classLike->stmts[$key];
            $nextStmt = $classLike->stmts[$key + 1];
            if ($stmt instanceof TraitUse && $nextStmt instanceof TraitUse) {
                continue;
            }
            if ($stmt instanceof EnumCase && $nextStmt instanceof EnumCase) {
                continue;
            }
            $endLine = $stmt->getEndLine();
            $rangeLine = $nextStmt->getStartLine() - $endLine;
            if ($rangeLine > 1) {
                $rangeLine = $this->commentResolver->resolveRangeLineFromComment($rangeLine, $endLine, $nextStmt);
            }
            // skip same line or < 0 that cause infinite loop or crash
            if ($rangeLine !== 1) {
                continue;
            }
            array_splice($classLike->stmts, $key + 1, 0, [new Nop()]);
            $hasChanged = \true;
            // iterate next
            return $this->processAddNewLine($classLike, $hasChanged, $key + 2);
        }
        if ($hasChanged) {
            return $classLike;
        }
        return null;
    }
}
