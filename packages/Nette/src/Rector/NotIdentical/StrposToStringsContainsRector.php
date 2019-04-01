<?php declare(strict_types=1);

namespace Rector\Nette\Rector\NotIdentical;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/CubLi
 * @see https://github.com/nette/utils/blob/bd961f49b211997202bda1d0fbc410905be370d4/src/Utils/Strings.php#L81
 */
final class StrposToStringsContainsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use Nette\Utils\Strings over bare string-functions', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi, my name is Tom';
        return strpos($name, 'Hi') !== false;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi, my name is Tom';
        return \Nette\Utils\Strings::contains($name, 'Hi');
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
        return [Node\Expr\BinaryOp\NotIdentical::class, Node\Expr\BinaryOp\Identical::class];
    }

    /**
     * @param Node\Expr\BinaryOp\NotIdentical|Node\Expr\BinaryOp\Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        $strpos = $this->matchStrposInComparisonToFalse($node);
        if ($strpos === null) {
            return null;
        }

        if (isset($strpos->args[2]) && ! $this->isValue($strpos->args[2]->value, 0)) {
            return null;
        }

        $containsStaticCall = new Node\Expr\StaticCall(new Node\Name\FullyQualified('Nette\Utils\Strings'), 'contains');
        $containsStaticCall->args[0] = $strpos->args[0];
        $containsStaticCall->args[1] = $strpos->args[1];

        if ($node instanceof Node\Expr\BinaryOp\Identical) {
            return new Node\Expr\BooleanNot($containsStaticCall);
        }

        return $containsStaticCall;
    }

    private function matchStrposInComparisonToFalse(Node\Expr\BinaryOp $binaryOp): ?Node\Expr\FuncCall
    {
        if ($this->isFalse($binaryOp->left)) {
            if (! $binaryOp->right instanceof Node\Expr\FuncCall) {
                return null;
            }

            if ($this->isName($binaryOp->right, 'strpos')) {
                return $binaryOp->right;
            }
        }

        if ($this->isFalse($binaryOp->right)) {
            if (! $binaryOp->left instanceof Node\Expr\FuncCall) {
                return null;
            }

            if ($this->isName($binaryOp->left, 'strpos')) {
                return $binaryOp->left;
            }
        }

        return null;
    }
}
