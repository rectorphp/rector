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
final class DowngradeClosureCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\MethodTypeAnalyzer
     */
    private $methodTypeAnalyzer;
    public function __construct(MethodTypeAnalyzer $methodTypeAnalyzer)
    {
        $this->methodTypeAnalyzer = $methodTypeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace Closure::call() by Closure::bindTo()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?FuncCall
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $methodCall = $this->createBindToCall($node);
        $item1Unpacked = \array_slice($node->args, 1);
        $args = \array_merge([new Arg($methodCall)], $item1Unpacked);
        return new FuncCall(new Name('call_user_func'), $args);
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if ($methodCall->args === []) {
            return \true;
        }
        return !$this->methodTypeAnalyzer->isCallTo($methodCall, Closure::class, 'call');
    }
    private function createBindToCall(MethodCall $methodCall) : MethodCall
    {
        $newObj = $methodCall->getArgs()[0];
        if ($newObj->value instanceof Variable) {
            $args = [$newObj, $newObj];
        } else {
            // we don't want the expression to be executed twice so we use array_fill() as a trick
            $args = [new Arg(new LNumber(0)), new Arg(new LNumber(2)), $newObj];
            $funcCall = new FuncCall(new Name('array_fill'), $args);
            $args = [new Arg($funcCall, \false, \true)];
        }
        return new MethodCall($methodCall->var, 'bindTo', $args);
    }
}
