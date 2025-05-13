<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\CodingStyle\NodeFactory\ArrayCallableToMethodCallFactory;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector\CallUserFuncArrayToVariadicRectorTest
 */
final class CallUserFuncArrayToVariadicRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ArrayCallableToMethodCallFactory $arrayCallableToMethodCallFactory;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ArrayCallableToMethodCallFactory $arrayCallableToMethodCallFactory, ValueResolver $valueResolver)
    {
        $this->arrayCallableToMethodCallFactory = $arrayCallableToMethodCallFactory;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace `call_user_func_array()` with variadic', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        call_user_func_array('some_function', $items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        some_function(...$items);
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'call_user_func_array')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArgValue = $node->getArgs()[0]->value;
        $secondArgValue = $node->getArgs()[1]->value;
        if ($firstArgValue instanceof String_) {
            $functionName = $this->valueResolver->getValue($firstArgValue);
            return $this->createFuncCall($secondArgValue, $functionName);
        }
        // method call
        if ($firstArgValue instanceof Array_) {
            return $this->createMethodCall($firstArgValue, $secondArgValue);
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ARRAY_SPREAD;
    }
    private function createFuncCall(Expr $expr, string $functionName) : FuncCall
    {
        $args = [$this->createUnpackedArg($expr)];
        return $this->nodeFactory->createFuncCall($functionName, $args);
    }
    private function createMethodCall(Array_ $array, Expr $secondExpr) : ?MethodCall
    {
        $methodCall = $this->arrayCallableToMethodCallFactory->create($array);
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        $methodCall->args[] = $this->createUnpackedArg($secondExpr);
        return $methodCall;
    }
    private function createUnpackedArg(Expr $expr) : Arg
    {
        return new Arg($expr, \false, \true);
    }
}
