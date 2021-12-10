<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
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
final class ArrayKeysAndInArrayToArrayKeyExistsRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace array_keys() and in_array() to array_key_exists()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($packageName, $values)
    {
        $keys = array_keys($values);
        return in_array($packageName, $keys, true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($packageName, $values)
    {
        return array_key_exists($packageName, $values);
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
        if (!$this->nodeNameResolver->isName($node, 'in_array')) {
            return null;
        }
        if (!isset($node->args[1])) {
            return null;
        }
        if (!$node->args[1] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $arrayVariable = $node->args[1]->value;
        /** @var Assign|Node|null $previousAssignArraysKeysFuncCall */
        $previousAssignArraysKeysFuncCall = $this->betterNodeFinder->findFirstPreviousOfNode($node, function (\PhpParser\Node $node) use($arrayVariable) : bool {
            // breaking out of scope
            if ($node instanceof \PhpParser\Node\FunctionLike) {
                return \true;
            }
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return !(bool) $this->betterNodeFinder->find($node, function (\PhpParser\Node $n) use($arrayVariable) : bool {
                    return $this->nodeComparator->areNodesEqual($arrayVariable, $n);
                });
            }
            if (!$this->nodeComparator->areNodesEqual($arrayVariable, $node->var)) {
                return \false;
            }
            if (!$node->expr instanceof \PhpParser\Node\Expr\FuncCall) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node->expr, 'array_keys');
        });
        if (!$previousAssignArraysKeysFuncCall instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        /** @var FuncCall $arrayKeysFuncCall */
        $arrayKeysFuncCall = $previousAssignArraysKeysFuncCall->expr;
        $this->removeNode($previousAssignArraysKeysFuncCall);
        return $this->createArrayKeyExists($node, $arrayKeysFuncCall);
    }
    private function createArrayKeyExists(\PhpParser\Node\Expr\FuncCall $inArrayFuncCall, \PhpParser\Node\Expr\FuncCall $arrayKeysFuncCall) : ?\PhpParser\Node\Expr\FuncCall
    {
        if (!isset($inArrayFuncCall->args[0])) {
            return null;
        }
        if (!$inArrayFuncCall->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if (!isset($arrayKeysFuncCall->args[0])) {
            return null;
        }
        if (!$arrayKeysFuncCall->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $arguments = [$inArrayFuncCall->args[0], $arrayKeysFuncCall->args[0]];
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('array_key_exists'), $arguments);
    }
}
