<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\UnusedForeachValueToArrayKeysRectorTest
 */
final class UnusedForeachValueToArrayKeysRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    public function __construct(\Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change foreach with unused $value but only $key, to array_keys()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $items = [];
        foreach ($values as $key => $value) {
            $items[$key] = null;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $items = [];
        foreach (array_keys($values) as $key) {
            $items[$key] = null;
        }
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
        return [\PhpParser\Node\Stmt\Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->keyVar === null) {
            return null;
        }
        // special case of nested array items
        if ($node->valueVar instanceof \PhpParser\Node\Expr\Array_) {
            $node->valueVar = $this->refactorArrayForeachValue($node->valueVar, $node);
            if ($node->valueVar->items !== []) {
                return null;
            }
        } elseif ($node->valueVar instanceof \PhpParser\Node\Expr\Variable) {
            if ($this->isVariableUsedInForeach($node->valueVar, $node)) {
                return null;
            }
        } else {
            return null;
        }
        if (!$this->isArrayType($node->expr)) {
            return null;
        }
        $this->removeForeachValueAndUseArrayKeys($node);
        return $node;
    }
    private function refactorArrayForeachValue(\PhpParser\Node\Expr\Array_ $array, \PhpParser\Node\Stmt\Foreach_ $foreach) : \PhpParser\Node\Expr\Array_
    {
        foreach ($array->items as $key => $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $value = $arrayItem->value;
            if (!$value instanceof \PhpParser\Node\Expr\Variable) {
                return $array;
            }
            if ($this->isVariableUsedInForeach($value, $foreach)) {
                continue;
            }
            unset($array->items[$key]);
        }
        return $array;
    }
    private function isVariableUsedInForeach(\PhpParser\Node\Expr\Variable $variable, \PhpParser\Node\Stmt\Foreach_ $foreach) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($foreach->stmts, function (\PhpParser\Node $node) use($variable) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $variable);
        });
    }
    private function removeForeachValueAndUseArrayKeys(\PhpParser\Node\Stmt\Foreach_ $foreach) : void
    {
        // remove key value
        $foreach->valueVar = $foreach->keyVar;
        $foreach->keyVar = null;
        $foreach->expr = $this->nodeFactory->createFuncCall('array_keys', [$foreach->expr]);
    }
    private function isArrayType(\PhpParser\Node\Expr $expr) : bool
    {
        $exprType = $this->getType($expr);
        if ($exprType instanceof \PHPStan\Type\ObjectType) {
            return \false;
        }
        return $exprType->isArray()->yes();
    }
}
