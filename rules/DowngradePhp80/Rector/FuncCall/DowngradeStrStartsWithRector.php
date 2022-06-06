<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Identical;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector\DowngradeStrStartsWithRectorTest
 */
final class DowngradeStrStartsWithRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade str_starts_with() to strncmp() version', [new CodeSample('str_starts_with($haystack, $needle);', 'strncmp($haystack, $needle, strlen($needle)) === 0;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class, BooleanNot::class];
    }
    /**
     * @param FuncCall|BooleanNot $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof FuncCall && $this->isName($node, 'str_starts_with')) {
            return $this->createIdentical($node);
        }
        if ($node instanceof BooleanNot) {
            $negatedCall = $node->expr;
            if ($negatedCall instanceof FuncCall && $this->isName($negatedCall, 'str_starts_with')) {
                return $this->createNotIdenticalStrncmpFuncCall($negatedCall);
            }
        }
        return null;
    }
    private function createIdentical(FuncCall $funcCall) : Identical
    {
        $strlenFuncCall = $this->createStrlenFuncCall($funcCall);
        $strncmpFuncCall = $this->createStrncmpFuncCall($funcCall, $strlenFuncCall);
        return new Identical($strncmpFuncCall, new LNumber(0));
    }
    private function createNotIdenticalStrncmpFuncCall(FuncCall $funcCall) : NotIdentical
    {
        $strlenFuncCall = $this->createStrlenFuncCall($funcCall);
        $strncmpFuncCall = $this->createStrncmpFuncCall($funcCall, $strlenFuncCall);
        return new NotIdentical($strncmpFuncCall, new LNumber(0));
    }
    private function createStrlenFuncCall(FuncCall $funcCall) : FuncCall
    {
        return new FuncCall(new Name('strlen'), [$funcCall->args[1]]);
    }
    private function createStrncmpFuncCall(FuncCall $funcCall, FuncCall $strlenFuncCall) : FuncCall
    {
        $newArgs = $funcCall->args;
        $newArgs[] = new Arg($strlenFuncCall);
        return new FuncCall(new Name('strncmp'), $newArgs);
    }
}
