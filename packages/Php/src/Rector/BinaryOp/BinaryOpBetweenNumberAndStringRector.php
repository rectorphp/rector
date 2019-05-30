<?php declare(strict_types=1);

namespace Rector\Php\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Scalar\LNumber;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/XPEEl
 * @see https://3v4l.org/ObNQZ
 */
final class BinaryOpBetweenNumberAndStringRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change binary operation between some number + string to PHP 7.1 compatible version',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5 + '';
        $value = 5.0 + 'hi';

        $name = 'Tom';
        $value = 5 * $name;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5 + 0;
        $value = 5.0 + 0

        $name = 'Tom';
        $value = 5 * 0;
    }
}
CODE_SAMPLE
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
        if ($node instanceof BinaryOp\Concat) {
            return null;
        }

        if ($this->isStringyType($node->left) && $this->isNumberType($node->right)) {
            $node->left = new LNumber(0);

            return $node;
        }

        if ($this->isStringyType($node->right) && $this->isNumberType($node->left)) {
            $node->right = new LNumber(0);

            return $node;
        }

        return null;
    }
}
