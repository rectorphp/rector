<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\BooleanAnd\RemoveAndTrueRector\RemoveAndTrueRectorTest
 */
final class RemoveAndTrueRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove and true that has no added value', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return true && 5 === 1;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 5 === 1;
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
        return [BooleanAnd::class];
    }

    /**
     * @param BooleanAnd $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isTrueOrBooleanAndTrues($node->left)) {
            return $node->right;
        }

        if ($this->isTrueOrBooleanAndTrues($node->right)) {
            return $node->left;
        }

        return null;
    }

    private function isTrueOrBooleanAndTrues(Node $node): bool
    {
        if ($this->valueResolver->isTrue($node)) {
            return true;
        }

        if (! $node instanceof BooleanAnd) {
            return false;
        }

        if (! $this->isTrueOrBooleanAndTrues($node->left)) {
            return false;
        }

        return $this->isTrueOrBooleanAndTrues($node->right);
    }
}
