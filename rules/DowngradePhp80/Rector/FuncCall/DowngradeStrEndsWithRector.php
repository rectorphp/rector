<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Identical;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\UnaryMinus;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector\DowngradeStrEndsWithRectorTest
 */
final class DowngradeStrEndsWithRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade str_ends_with() to strncmp() version', [new CodeSample('str_ends_with($haystack, $needle);', '"" === $needle || ("" !== $haystack && 0 === substr_compare($haystack, $needle, -\\strlen($needle)));')]);
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
        if ($node instanceof FuncCall && $this->isName($node->name, 'str_ends_with')) {
            return new Identical($this->createSubstrCompareFuncCall($node), new LNumber(0));
        }
        if ($node instanceof BooleanNot) {
            $funcCall = $node->expr;
            if ($funcCall instanceof FuncCall && $this->isName($funcCall->name, 'str_ends_with')) {
                return new NotIdentical($this->createSubstrCompareFuncCall($funcCall), new LNumber(0));
            }
        }
        return null;
    }
    private function createSubstrCompareFuncCall(FuncCall $funcCall) : FuncCall
    {
        $args = $funcCall->args;
        $strlenFuncCall = $this->createStrlenFuncCall($args[1]->value);
        $args[] = new Arg(new UnaryMinus($strlenFuncCall));
        return new FuncCall(new Name('substr_compare'), $args);
    }
    private function createStrlenFuncCall(Expr $expr) : FuncCall
    {
        return new FuncCall(new Name('strlen'), [new Arg($expr)]);
    }
}
