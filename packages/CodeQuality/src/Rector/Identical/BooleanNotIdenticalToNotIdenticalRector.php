<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/GoEPq
 */
final class BooleanNotIdenticalToNotIdenticalRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Negated identical boolean compare to not identical compare (does not apply to non-bool values)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $a = true;
        $b = false;

        var_dump(! $a === $b); // true
        var_dump(! ($a === $b)); // true
        var_dump($a !== $b); // true
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $a = true;
        $b = false;

        var_dump($a !== $b); // true
        var_dump($a !== $b); // true
        var_dump($a !== $b); // true
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
        return [Identical::class, Node\Expr\BooleanNot::class];
    }

    /**
     * @param Identical|Node\Expr\BooleanNot $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identical) {
            return $this->processIdentical($node);
        }

        if ($node->expr instanceof Identical) {
            $identical = $node->expr;
            if (! $this->isBoolType($identical->left)) {
                return null;
            }

            if (! $this->isBoolType($identical->right)) {
                return null;
            }

            return new Node\Expr\BinaryOp\NotIdentical($identical->left, $identical->right);
        }

        return null;
    }

    private function processIdentical(Identical $identical): ?Node\Expr\BinaryOp\NotIdentical
    {
        if (! $this->isBoolType($identical->left)) {
            return null;
        }

        if (! $this->isBoolType($identical->right)) {
            return null;
        }

        if ($identical->left instanceof Node\Expr\BooleanNot) {
            return new Node\Expr\BinaryOp\NotIdentical($identical->left->expr, $identical->right);
        }

        return null;
    }
}
