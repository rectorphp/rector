<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\StrlenZeroToIdenticalEmptyStringRectorTest
 */
final class StrlenZeroToIdenticalEmptyStringRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes strlen comparison to 0 to direct empty string compare', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $empty = strlen($value) === 0;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $empty = $value === '';
    }
}
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
        $variable = null;
        if ($node->left instanceof \PhpParser\Node\Expr\FuncCall) {
            if (!$this->isName($node->left, 'strlen')) {
                return null;
            }
            if (!$this->valueResolver->isValue($node->right, 0)) {
                return null;
            }
            $variable = $node->left->args[0]->value;
        } elseif ($node->right instanceof \PhpParser\Node\Expr\FuncCall) {
            if (!$this->isName($node->right, 'strlen')) {
                return null;
            }
            if (!$this->valueResolver->isValue($node->left, 0)) {
                return null;
            }
            $variable = $node->right->args[0]->value;
        } else {
            return null;
        }
        /** @var Expr $variable */
        return new \PhpParser\Node\Expr\BinaryOp\Identical($variable, new \PhpParser\Node\Scalar\String_(''));
    }
}
