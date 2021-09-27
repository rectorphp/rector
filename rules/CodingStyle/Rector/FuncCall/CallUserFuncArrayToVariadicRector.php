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
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.call-user-func-array.php#117655
 * @changelog https://3v4l.org/CBWt9
 *
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector\CallUserFuncArrayToVariadicRectorTest
 */
final class CallUserFuncArrayToVariadicRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var \Rector\CodingStyle\NodeFactory\ArrayCallableToMethodCallFactory
     */
    private $arrayCallableToMethodCallFactory;
    /**
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\CodingStyle\NodeFactory\ArrayCallableToMethodCallFactory $arrayCallableToMethodCallFactory, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->arrayCallableToMethodCallFactory = $arrayCallableToMethodCallFactory;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace call_user_func_array() with variadic', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'call_user_func_array')) {
            return null;
        }
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($node->args, [0, 1])) {
            return null;
        }
        /** @var Arg $firstArg */
        $firstArg = $node->args[0];
        $firstArgValue = $firstArg->value;
        /** @var Arg $secondArg */
        $secondArg = $node->args[1];
        $secondArgValue = $secondArg->value;
        if ($firstArgValue instanceof \PhpParser\Node\Scalar\String_) {
            $functionName = $this->valueResolver->getValue($firstArgValue);
            return $this->createFuncCall($secondArgValue, $functionName);
        }
        // method call
        if ($firstArgValue instanceof \PhpParser\Node\Expr\Array_) {
            return $this->createMethodCall($firstArgValue, $secondArgValue);
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::ARRAY_SPREAD;
    }
    private function createFuncCall(\PhpParser\Node\Expr $expr, string $functionName) : \PhpParser\Node\Expr\FuncCall
    {
        $args = [];
        $args[] = $this->createUnpackedArg($expr);
        return $this->nodeFactory->createFuncCall($functionName, $args);
    }
    private function createMethodCall(\PhpParser\Node\Expr\Array_ $array, \PhpParser\Node\Expr $secondExpr) : ?\PhpParser\Node\Expr\MethodCall
    {
        $methodCall = $this->arrayCallableToMethodCallFactory->create($array);
        if (!$methodCall instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $methodCall->args[] = $this->createUnpackedArg($secondExpr);
        return $methodCall;
    }
    private function createUnpackedArg(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Arg
    {
        return new \PhpParser\Node\Arg($expr, \false, \true);
    }
}
