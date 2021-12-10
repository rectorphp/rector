<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector\SimplifyInArrayValuesRectorTest
 */
final class SimplifyInArrayValuesRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes unneeded array_values() in in_array() call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('in_array("key", array_values($array), true);', 'in_array("key", $array, true);')]);
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
        if (!$this->isName($node, 'in_array')) {
            return null;
        }
        if (!isset($node->args[1])) {
            return null;
        }
        if (!$node->args[1] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if (!$node->args[1]->value instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        /** @var FuncCall $innerFunCall */
        $innerFunCall = $node->args[1]->value;
        if (!$this->isName($innerFunCall, 'array_values')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $node->args[1] = $innerFunCall->args[0];
        return $node;
    }
}
