<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector\DowngradeFreadFwriteFalsyToNegationRectorTest
 */
final class DowngradeFreadFwriteFalsyToNegationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const FUNC_FREAD_FWRITE = ['fread', 'fwrite'];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes fread() or fwrite() compare to false to negation check', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
fread($handle, $length) === false;
fwrite($fp, '1') === false;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
!fread($handle, $length);
!fwrite($fp, '1');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class];
    }
    /**
     * @param Identical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $compareValue = $this->getCompareValue($node);
        if (!$compareValue instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if (!$this->valueResolver->isFalse($compareValue)) {
            return null;
        }
        return new \PhpParser\Node\Expr\BooleanNot($this->getFunction($node));
    }
    private function getCompareValue(\PhpParser\Node\Expr\BinaryOp\Identical $identical) : ?\PhpParser\Node\Expr
    {
        if ($identical->left instanceof \PhpParser\Node\Expr\FuncCall && $this->isNames($identical->left, self::FUNC_FREAD_FWRITE)) {
            return $identical->right;
        }
        if (!$identical->right instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->isNames($identical->right, self::FUNC_FREAD_FWRITE)) {
            return null;
        }
        return $identical->left;
    }
    private function getFunction(\PhpParser\Node\Expr\BinaryOp\Identical $identical) : \PhpParser\Node\Expr\FuncCall
    {
        /** @var FuncCall $funcCall */
        $funcCall = $identical->left instanceof \PhpParser\Node\Expr\FuncCall ? $identical->left : $identical->right;
        return $funcCall;
    }
}
