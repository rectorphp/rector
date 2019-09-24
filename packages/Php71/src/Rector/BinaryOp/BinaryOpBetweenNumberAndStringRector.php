<?php declare(strict_types=1);

namespace Rector\Php71\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/XPEEl
 * @see https://3v4l.org/ObNQZ
 * @see \Rector\Php71\Tests\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector\BinaryOpBetweenNumberAndStringRectorTest
 */
final class BinaryOpBetweenNumberAndStringRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change binary operation between some number + string to PHP 7.1 compatible version',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    public function run()
    {
        $value = 5 + '';
        $value = 5.0 + 'hi';
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeClass
{
    public function run()
    {
        $value = 5 + 0;
        $value = 5.0 + 0
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [BinaryOp::class];
    }

    /**
     * @param BinaryOp $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Concat) {
            return null;
        }

        if ($node->left instanceof String_ && $this->isNumberType($node->right)) {
            $node->left = new LNumber(0);

            return $node;
        }

        if ($node->right instanceof String_ && $this->isNumberType($node->left)) {
            $node->right = new LNumber(0);

            return $node;
        }

        return null;
    }
}
