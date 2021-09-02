<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Stmt;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\NewlineAfterStatementRectorTest
 */
final class NewlineAfterStatementRector extends AbstractRector
{
    /**
     * @var array<class-string<Node>>
     */
    private const STMTS_TO_HAVE_NEXT_NEWLINE = [
        ClassMethod::class,
        Function_::class,
        Property::class,
        If_::class,
        Foreach_::class,
        Do_::class,
        While_::class,
        For_::class,
        ClassConst::class,
        Namespace_::class,
        TryCatch::class,
        Class_::class,
        Trait_::class,
        Interface_::class,
        Switch_::class,
    ];

    /**
     * @var array<string, true>
     */
    private array $stmtsHashed = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add new line after statements to tidify code',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function test()
    {
    }
    public function test2()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function test()
    {
    }

    public function test2()
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Stmt::class];
    }

    /**
     * @param Stmt $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! in_array($node::class, self::STMTS_TO_HAVE_NEXT_NEWLINE, true)) {
            return null;
        }

        $hash = spl_object_hash($node);
        if (isset($this->stmtsHashed[$hash])) {
            return null;
        }

        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Node) {
            return null;
        }

        if ($this->shouldSkip($nextNode)) {
            return null;
        }

        $endLine = $node->getEndLine();
        $line = $nextNode->getLine();
        $rangeLine = $line - $endLine;

        if ($rangeLine > 1) {
            $comments = $nextNode->getAttribute(AttributeKey::COMMENTS);
            if ($comments === null) {
                return null;
            }

            if (! isset($comments[0])) {
                return null;
            }

            $line = $comments[0]->getLine();
            $rangeLine = $line - $endLine;

            if ($rangeLine > 1) {
                return null;
            }
        }

        $this->stmtsHashed[$hash] = true;
        $this->addNodeAfterNode(new Nop(), $node);

        return $node;
    }

    private function shouldSkip(Node $nextNode): bool
    {
        return $nextNode instanceof Else_ || $nextNode instanceof ElseIf_ || $nextNode instanceof Catch_ || $nextNode instanceof Finally_;
    }
}
