<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyBoolIdenticalTrueRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Symplify bool value compare to true or false', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE) === TRUE;
         $match = in_array($value, $items, TRUE) !== FALSE;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE);
         $match = in_array($value, $items, TRUE);
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
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isBoolType($node->left) && ! $this->isBool($node->left)) {
            if ($node instanceof Identical) {
                if ($this->isTrue($node->right)) {
                    return $node->left;
                }

                if ($this->isFalse($node->right)) {
                    return new BooleanNot($node->left);
                }
            }

            if ($node instanceof NotIdentical) {
                if ($this->isFalse($node->right)) {
                    return $node->left;
                }

                if ($this->isTrue($node->right)) {
                    return new BooleanNot($node->left);
                }
            }
        }

        if ($this->isBoolType($node->right) && ! $this->isBool($node->right)) {
            if ($node instanceof Identical) {
                if ($this->isTrue($node->left)) {
                    return $node->right;
                }

                if ($this->isFalse($node->left)) {
                    return new BooleanNot($node->right);
                }
            }

            if ($node instanceof NotIdentical) {
                if ($this->isFalse($node->left)) {
                    return $node->right;
                }

                if ($this->isTrue($node->left)) {
                    return new BooleanNot($node->right);
                }
            }
        }

        return null;
    }
}
