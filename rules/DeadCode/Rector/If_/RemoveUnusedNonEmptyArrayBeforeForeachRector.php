<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeManipulator\CountManipulator;
use Rector\DeadCode\UselessIfCondBeforeForeachDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\RemoveUnusedNonEmptyArrayBeforeForeachRectorTest
 */
final class RemoveUnusedNonEmptyArrayBeforeForeachRector extends AbstractRector
{
    public function __construct(
        private CountManipulator $countManipulator,
        private IfManipulator $ifManipulator,
        private UselessIfCondBeforeForeachDetector $uselessIfCondBeforeForeachDetector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove unused if check to non-empty array before foreach of the array',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [];
        if ($values !== []) {
            foreach ($values as $value) {
                echo $value;
            }
        }
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [];
        foreach ($values as $value) {
            echo $value;
        }
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
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isUselessBeforeForeachCheck($node)) {
            return null;
        }

        $stmt = $node->stmts[0];

        $ifComments = $node->getAttribute(AttributeKey::COMMENTS) ?? [];
        $stmtComments = $stmt->getAttribute(AttributeKey::COMMENTS) ?? [];

        $comments = array_merge($ifComments, $stmtComments);
        $stmt->setAttribute(AttributeKey::COMMENTS, $comments);

        return $stmt;
    }

    private function isUselessBeforeForeachCheck(If_ $if): bool
    {
        if (! $this->ifManipulator->isIfWithOnly($if, Foreach_::class)) {
            return false;
        }

        /** @var Foreach_ $foreach */
        $foreach = $if->stmts[0];
        $foreachExpr = $foreach->expr;

        if ($this->uselessIfCondBeforeForeachDetector->isMatchingNotIdenticalEmptyArray($if, $foreachExpr)) {
            return true;
        }

        if ($this->uselessIfCondBeforeForeachDetector->isMatchingNotEmpty($if, $foreachExpr)) {
            return true;
        }

        return $this->countManipulator->isCounterHigherThanOne($if->cond, $foreachExpr);
    }
}
