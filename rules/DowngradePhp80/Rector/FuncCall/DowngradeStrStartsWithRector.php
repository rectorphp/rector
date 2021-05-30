<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector\DowngradeStrStartsWithRectorTest
 */
final class DowngradeStrStartsWithRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade str_starts_with() to strncmp() version', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('str_starts_with($haystack, $needle);', 'strncmp($haystack, $needle, strlen($needle)) === 0;')]);
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
        if ($node instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($node, 'str_starts_with')) {
            return $this->createIdentical($node);
        }
        if ($node instanceof \PhpParser\Node\Expr\BooleanNot) {
            $negatedCall = $node->expr;
            if ($negatedCall instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($negatedCall, 'str_starts_with')) {
                return $this->createNotIdenticalStrncmpFuncCall($negatedCall);
            }
        }
        return null;
    }
    private function createIdentical(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\BinaryOp\Identical
    {
        $strlenFuncCall = $this->createStrlenFuncCall($funcCall);
        $strncmpFuncCall = $this->createStrncmpFuncCall($funcCall, $strlenFuncCall);
        return new \PhpParser\Node\Expr\BinaryOp\Identical($strncmpFuncCall, new \PhpParser\Node\Scalar\LNumber(0));
    }
    private function createNotIdenticalStrncmpFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\BinaryOp\NotIdentical
    {
        $strlenFuncCall = $this->createStrlenFuncCall($funcCall);
        $strncmpFuncCall = $this->createStrncmpFuncCall($funcCall, $strlenFuncCall);
        return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($strncmpFuncCall, new \PhpParser\Node\Scalar\LNumber(0));
    }
    private function createStrlenFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\FuncCall
    {
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('strlen'), [$funcCall->args[1]]);
    }
    private function createStrncmpFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\FuncCall $strlenFuncCall) : \PhpParser\Node\Expr\FuncCall
    {
        $newArgs = $funcCall->args;
        $newArgs[] = new \PhpParser\Node\Arg($strlenFuncCall);
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('strncmp'), $newArgs);
    }
}
