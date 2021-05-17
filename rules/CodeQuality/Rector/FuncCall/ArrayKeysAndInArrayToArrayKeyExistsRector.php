<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\ArrayKeysAndInArrayToArrayKeyExistsRectorTest
 */
final class ArrayKeysAndInArrayToArrayKeyExistsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace array_keys() and in_array() to array_key_exists()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($packageName, $values)
    {
        $keys = array_keys($values);
        return in_array($packageName, $keys, true);
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($packageName, $values)
    {
        return array_key_exists($packageName, $values);
    }
}
CODE_SAMPLE
                ),
            ]
        );
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
        if (! $this->nodeNameResolver->isName($node, 'in_array')) {
            return null;
        }

        $arrayVariable = $node->args[1]->value;

        /** @var Assign|Node|null $previousAssignArraysKeysFuncCall */
        $previousAssignArraysKeysFuncCall = $this->betterNodeFinder->findFirstPreviousOfNode($node, function (
            Node $node
        ) use ($arrayVariable): bool {
            // breaking out of scope
            if ($node instanceof FunctionLike) {
                return true;
            }

            if (! $node instanceof Assign) {
                return ! (bool) $this->betterNodeFinder->find($node, function (Node $n) use ($arrayVariable) {
                    return $this->nodeComparator->areNodesEqual($arrayVariable, $n);
                });
            }

            if (! $this->nodeComparator->areNodesEqual($arrayVariable, $node->var)) {
                return false;
            }

            if (! $node->expr instanceof FuncCall) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->expr, 'array_keys');
        });

        if (! $previousAssignArraysKeysFuncCall instanceof Assign) {
            return null;
        }

        /** @var FuncCall $arrayKeysFuncCall */
        $arrayKeysFuncCall = $previousAssignArraysKeysFuncCall->expr;

        $this->removeNode($previousAssignArraysKeysFuncCall);

        return $this->createArrayKeyExists($node, $arrayKeysFuncCall);
    }

    private function createArrayKeyExists(FuncCall $inArrayFuncCall, FuncCall $arrayKeysFuncCall): FuncCall
    {
        $arguments = [$inArrayFuncCall->args[0], $arrayKeysFuncCall->args[0]];

        return new FuncCall(new Name('array_key_exists'), $arguments);
    }
}
