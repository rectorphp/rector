<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Concat;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Cast\String_;
use Rector\Core\Rector\AbstractRector;

/**
 * @see \Rector\DeadCode\Tests\Rector\Concat\RemoveConcatAutocastRector\RemoveConcatAutocastRectorTest
 */
final class RemoveConcatAutocastRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Remove (string) casting when it comes to concat, that does this by default',
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
class SomeConcatingClass
{
    public function run($value)
    {
        return 'hi ' . (string) $value;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeConcatingClass
{
    public function run($value)
    {
        return 'hi ' . $value;
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
        return [Concat::class];
    }

    /**
     * @param Concat $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->left instanceof String_ && ! $node->right instanceof String_) {
            return null;
        }

        $node->left = $this->removeStringCast($node->left);
        $node->right = $this->removeStringCast($node->right);

        return $node;
    }

    private function removeStringCast(Expr $expr): Expr
    {
        return $expr instanceof String_ ? $expr->expr : $expr;
    }
}
