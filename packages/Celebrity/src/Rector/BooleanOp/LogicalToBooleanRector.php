<?php declare(strict_types=1);

namespace Rector\Celebrity\Rector\BooleanOp;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/questions/5998309/logical-operators-or-or
 * @see https://stackoverflow.com/questions/9454870/php-xor-how-to-use-with-if
 */
final class LogicalToBooleanRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change OR, AND to ||, && with more common understanding', [
            new CodeSample(
                <<<'CODE_SAMPLE'
if ($f = false or true) {
    return $f;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
if (($f = false) || true) {
    return $f;
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
        return [LogicalOr::class, LogicalAnd::class];
    }

    /**
     * @param LogicalOr|LogicalAnd $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof LogicalOr) {
            return new BooleanOr($node->left, $node->right);
        }

        if ($node instanceof LogicalAnd) {
            return new BooleanAnd($node->left, $node->right);
        }

        return null;
    }
}
