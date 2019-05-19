<?php declare(strict_types=1);

namespace Rector\Php\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
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
        $value = 5 + 'hi';
        $value = 5 + 'Tom';

        $value = 5 * '';
        $value = 5 * 'hi';

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
        $value = 5 + 0
        $value = 5 + 0;

        $value = 5 * 0;
        $value = 5 * 0;

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
        if ($this->isStringyType($node->left) && $this->isIntegerType($node->right)) {
            $node->left = new Node\Scalar\LNumber(0);
            return $node;
        }

        if ($this->isStringyType($node->right) && $this->isIntegerType($node->left)) {
            $node->right = new Node\Scalar\LNumber(0);
            return $node;
        }

        return null;
    }
}
