<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector\DowngradeStrEndsWithRectorTest
 */
final class DowngradeStrEndsWithRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade str_ends_with() to strncmp() version', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('str_ends_with($haystack, $needle);', '"" === $needle || ("" !== $haystack && 0 === substr_compare($haystack, $needle, -\\strlen($needle)));')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\BooleanNot::class];
    }
    /**
     * @param FuncCall|BooleanNot $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($node->name, 'str_ends_with')) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($this->createSubstrCompareFuncCall($node), new \PhpParser\Node\Scalar\LNumber(0));
        }
        if ($node instanceof \PhpParser\Node\Expr\BooleanNot) {
            $funcCall = $node->expr;
            if ($funcCall instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($funcCall->name, 'str_ends_with')) {
                return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($this->createSubstrCompareFuncCall($funcCall), new \PhpParser\Node\Scalar\LNumber(0));
            }
        }
        return null;
    }
    private function createSubstrCompareFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\FuncCall
    {
        $args = $funcCall->args;
        $strlenFuncCall = $this->createStrlenFuncCall($args[1]->value);
        $args[] = new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\UnaryMinus($strlenFuncCall));
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('substr_compare'), $args);
    }
    private function createStrlenFuncCall(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\FuncCall
    {
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('strlen'), [new \PhpParser\Node\Arg($expr)]);
    }
}
