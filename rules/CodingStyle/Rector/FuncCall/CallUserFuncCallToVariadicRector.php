<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://www.php.net/manual/en/function.call-user-func-array.php#117655
 * @changelog https://3v4l.org/CBWt9
 *
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector\CallUserFuncCallToVariadicRectorTest
 */
final class CallUserFuncCallToVariadicRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace call_user_func_call with variadic', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        call_user_func_array('some_function', $items);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        some_function(...$items);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'call_user_func_array')) {
            return null;
        }

        $firstArgValue = $node->args[0]->value;
        $secondArgValue = $node->args[1]->value;

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

    private function createFuncCall(Expr $expr, string $functionName): FuncCall
    {
        $args = [];
        $args[] = new Arg($expr, false, true);

        return $this->nodeFactory->createFuncCall($functionName, $args);
    }

    private function createMethodCall(Array_ $array, Expr $secondExpr): ?MethodCall
    {
        if (count($array->items) !== 2) {
            return null;
        }

        $firstItem = $array->items[0];
        $secondItem = $array->items[1];

        if (! $firstItem instanceof ArrayItem) {
            return null;
        }

        if (! $secondItem instanceof ArrayItem) {
            return null;
        }

        if ($firstItem->value instanceof PropertyFetch) {
            if (! $secondItem->value instanceof String_) {
                return null;
            }

            $string = $secondItem->value;
            $methodName = $string->value;

            return new MethodCall($firstItem->value, $methodName, [new Arg($secondExpr, false, true)]);
        }

        return null;
    }
}
