<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PhpParser\Node\Expr\BooleanNot;

/**
 * @see \Rector\DowngradePhp74\Tests\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector\DowngradeFreadFwriteFalsyToNegationRectorTest
 */
final class DowngradeFreadFwriteFalsyToNegationRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes fread() or fwrite() compare to false to negation check',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
fread($handle, $length) === false;
fwrite($fp, '1') === false;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
!fread($handle, $length);
!fwrite($fp, '1');
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class];
    }

    /**
     * @param Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        $compareValue = $this->getCompareValue($node);
        if ($compareValue === null) {
            return null;
        }

        if (! $this->isFalse($compareValue)) {
            return null;
        }

        return new BooleanNot($this->getFunction($node));
    }

    private function getCompareValue(Identical $identical): ?Expr
    {
        if ($identical->left instanceof FuncCall && $this->isNames($identical->left, ['fread', 'fwrite'])) {
            return $identical->right;
        }

        if ($identical->right instanceof FuncCall && $this->isNames($identical->right, ['fread', 'fwrite'])) {
            return $identical->left;
        }

        return null;
    }

    private function getFunction(Identical $identical): FuncCall
    {
        return $identical->left instanceof FuncCall
            ? $identical->left
            : $identical->right;
    }
}
