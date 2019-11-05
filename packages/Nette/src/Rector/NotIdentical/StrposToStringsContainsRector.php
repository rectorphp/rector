<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\NotIdentical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/CubLi
 * @see https://github.com/nette/utils/blob/bd961f49b211997202bda1d0fbc410905be370d4/src/Utils/Strings.php#L81
 * @see \Rector\Nette\Tests\Rector\NotIdentical\StrposToStringsContainsRector\StrposToStringsContainsRectorTest
 */
final class StrposToStringsContainsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use Nette\Utils\Strings over bare string-functions', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $name = 'Hi, my name is Tom';
        return strpos($name, 'Hi') !== false;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $name = 'Hi, my name is Tom';
        return \Nette\Utils\Strings::contains($name, 'Hi');
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [NotIdentical::class, Identical::class];
    }

    /**
     * @param NotIdentical|Identical $node
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

        $containsStaticCall = $this->createStaticCall('Nette\Utils\Strings', 'contains');
        $containsStaticCall->args[0] = $strpos->args[0];
        $containsStaticCall->args[1] = $strpos->args[1];

        if ($node instanceof Identical) {
            return new BooleanNot($containsStaticCall);
        }

        return $containsStaticCall;
    }

    private function matchStrposInComparisonToFalse(BinaryOp $binaryOp): ?FuncCall
    {
        if ($this->isFalse($binaryOp->left)) {
            if (! $binaryOp->right instanceof FuncCall) {
                return null;
            }

            if ($this->isName($binaryOp->right, 'strpos')) {
                return $binaryOp->right;
            }
        }

        if ($this->isFalse($binaryOp->right)) {
            if (! $binaryOp->left instanceof FuncCall) {
                return null;
            }

            if ($this->isName($binaryOp->left, 'strpos')) {
                return $binaryOp->left;
            }
        }

        return null;
    }
}
