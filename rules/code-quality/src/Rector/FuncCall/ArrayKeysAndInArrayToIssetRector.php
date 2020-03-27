<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\ArrayKeysAndInArrayToIssetRector\ArrayKeysAndInArrayToIssetRectorTest
 */
final class ArrayKeysAndInArrayToIssetRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace array_keys() and in_array() to isset', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($packageName, $values)
    {
        $keys = array_keys($values);
        return in_array($packageName, $keys, true);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($packageName, $values)
    {
        return isset($values[$packageName]));
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
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
        if (! $this->isFuncCallName($node, 'in_array')) {
            return null;
        }

        $arrayVariable = $node->args[1]->value;

        /** @var Assign|Node|null $previousAssignArraysKeysFuncCall */
        $previousAssignArraysKeysFuncCall = $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use (
            $arrayVariable
        ) {
            // breaking out of scope
            if ($node instanceof FunctionLike) {
                return true;
            }

            if (! $node instanceof Assign) {
                return false;
            }

            if (! $this->areNodesEqual($arrayVariable, $node->var)) {
                return false;
            }

            return $this->isFuncCallName($node->expr, 'array_keys');
        });

        if (! $previousAssignArraysKeysFuncCall instanceof Assign) {
            return null;
        }

        /** @var FuncCall $arrayKeysFuncCall */
        $arrayKeysFuncCall = $previousAssignArraysKeysFuncCall->expr;

        $this->removeNode($previousAssignArraysKeysFuncCall);

        return $this->createIsset($node, $arrayKeysFuncCall);
    }

    private function createIsset(FuncCall $inArrayFuncCall, FuncCall $arrayKeysFuncCall): FuncCall
    {
        $dimFetch = new ArrayDimFetch($arrayKeysFuncCall->args[0]->value, $inArrayFuncCall->args[0]->value);

        return new FuncCall(new Name('isset'), [new Arg($dimFetch)]);
    }
}
