<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\MethodCall;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeAnalyzer\MethodTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/closure_apply
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector\DowngradeClosureCallRectorTest
 */
final class DowngradeClosureCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\MethodTypeAnalyzer
     */
    private $methodTypeAnalyzer;
    public function __construct(\Rector\NodeTypeResolver\TypeAnalyzer\MethodTypeAnalyzer $methodTypeAnalyzer)
    {
        $this->methodTypeAnalyzer = $methodTypeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace Closure::call() by Closure::bindTo()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$closure->call($newObj, ...$args);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
call_user_func($closure->bindTo($newObj, $newObj), ...$args);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\FuncCall
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $methodCall = $this->createBindToCall($node);
        $item1Unpacked = \array_slice($node->args, 1);
        $args = \array_merge([new \PhpParser\Node\Arg($methodCall)], $item1Unpacked);
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('call_user_func'), $args);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if ($methodCall->args === []) {
            return \true;
        }
        return !$this->methodTypeAnalyzer->isCallTo($methodCall, \Closure::class, 'call');
    }
    private function createBindToCall(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr\MethodCall
    {
        $newObj = $methodCall->args[0];
        if ($newObj->value instanceof \PhpParser\Node\Expr\Variable) {
            $args = [$newObj, $newObj];
        } else {
            // we don't want the expression to be executed twice so we use array_fill() as a trick
            $args = [new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\LNumber(0)), new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\LNumber(2)), $newObj];
            $funcCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('array_fill'), $args);
            $args = [new \PhpParser\Node\Arg($funcCall, \false, \true)];
        }
        return new \PhpParser\Node\Expr\MethodCall($methodCall->var, 'bindTo', $args);
    }
}
