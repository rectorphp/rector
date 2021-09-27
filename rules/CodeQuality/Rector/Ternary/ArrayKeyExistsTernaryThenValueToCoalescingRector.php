<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/f7itn
 *
 * @see \Rector\Tests\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector\ArrayKeyExistsTernaryThenValueToCoalescingRectorTest
 */
final class ArrayKeyExistsTernaryThenValueToCoalescingRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change array_key_exists() ternary to coalesing', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($values, $keyToMatch)
    {
        $result = array_key_exists($keyToMatch, $values) ? $values[$keyToMatch] : null;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($values, $keyToMatch)
    {
        $result = $values[$keyToMatch] ?? null;
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
        return [\PhpParser\Node\Expr\Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->cond instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->isName($node->cond, 'array_key_exists')) {
            return null;
        }
        if (!$node->if instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        if (!$this->areArrayKeysExistsArgsMatchingDimFetch($node->cond, $node->if)) {
            return null;
        }
        if (!$this->valueResolver->isNull($node->else)) {
            return null;
        }
        return new \PhpParser\Node\Expr\BinaryOp\Coalesce($node->if, $node->else);
    }
    /**
     * Equals if:
     *
     * array_key_exists($key, $values);
     * =
     * $values[$key]
     */
    private function areArrayKeysExistsArgsMatchingDimFetch(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        $firstArg = $funcCall->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        $keyExpr = $firstArg->value;
        $secondArg = $funcCall->args[1];
        if (!$secondArg instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        $valuesExpr = $secondArg->value;
        if (!$this->nodeComparator->areNodesEqual($arrayDimFetch->var, $valuesExpr)) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($arrayDimFetch->dim, $keyExpr);
    }
}
