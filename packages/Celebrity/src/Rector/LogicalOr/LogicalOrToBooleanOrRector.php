<?php declare(strict_types=1);

namespace Rector\Celebrity\Rector\LogicalOr;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/questions/5998309/logical-operators-or-or
 */
final class LogicalOrToBooleanOrRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change OR to || with more common understanding', [
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
        return [LogicalOr::class];
    }

    /**
     * @param LogicalOr $node
     */
    public function refactor(Node $node): ?Node
    {
        return new BooleanOr($node->left, $node->right);
    }
}
