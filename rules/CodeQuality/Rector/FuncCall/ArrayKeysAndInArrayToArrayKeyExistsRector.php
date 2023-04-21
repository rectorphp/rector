<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace array_keys() and in_array() to array_key_exists()', [new CodeSample(<<<'CODE_SAMPLE'
function run($packageName, $values)
{
    $keys = array_keys($values);
    return in_array($packageName, $keys, true);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run($packageName, $values)
{
    return array_key_exists($packageName, $values);
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
        if (!$this->nodeNameResolver->isName($node, 'in_array')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        $secondArg = $args[1];
        $arrayVariable = $secondArg->value;
        $previousAssignArraysKeysFuncCall = $this->findPreviousAssignToArrayKeys($node, $arrayVariable);
        if ($previousAssignArraysKeysFuncCall instanceof Assign) {
            /** @var FuncCall $arrayKeysFuncCall */
            $arrayKeysFuncCall = $previousAssignArraysKeysFuncCall->expr;
            $this->removeNode($previousAssignArraysKeysFuncCall);
            return $this->createArrayKeyExists($node, $arrayKeysFuncCall);
        }
        if ($arrayVariable instanceof FuncCall && $this->isName($arrayVariable, 'array_keys')) {
            $arrayKeysFuncCallArgs = $arrayVariable->getArgs();
            if (\count($arrayKeysFuncCallArgs) > 1) {
                return null;
            }
            // unwrap array func call
            $secondArg->value = $arrayKeysFuncCallArgs[0]->value;
            $node->name = new Name('array_key_exists');
            unset($node->args[2]);
            return $node;
        }
        return null;
    }
    private function createArrayKeyExists(FuncCall $inArrayFuncCall, FuncCall $arrayKeysFuncCall) : ?FuncCall
    {
        if (!isset($inArrayFuncCall->args[0])) {
            return null;
        }
        if (!$inArrayFuncCall->args[0] instanceof Arg) {
            return null;
        }
        if (!isset($arrayKeysFuncCall->args[0])) {
            return null;
        }
        if (!$arrayKeysFuncCall->args[0] instanceof Arg) {
            return null;
        }
        $arguments = [$inArrayFuncCall->args[0], $arrayKeysFuncCall->args[0]];
        return new FuncCall(new Name('array_key_exists'), $arguments);
    }
    /**
     * @return null|\PhpParser\Node|\PhpParser\Node\FunctionLike
     */
    private function findPreviousAssignToArrayKeys(FuncCall $funcCall, Expr $expr)
    {
        return $this->betterNodeFinder->findFirstPrevious($funcCall, function (Node $node) use($expr) : bool {
            // breaking out of scope
            if ($node instanceof FunctionLike) {
                return \true;
            }
            if (!$node instanceof Assign) {
                return !(bool) $this->betterNodeFinder->findFirst($node, function (Node $subNode) use($expr) : bool {
                    return $this->nodeComparator->areNodesEqual($expr, $subNode);
                });
            }
            if (!$this->nodeComparator->areNodesEqual($expr, $node->var)) {
                return \false;
            }
            if (!$node->expr instanceof FuncCall) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node->expr, 'array_keys');
        });
    }
}
