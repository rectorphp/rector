<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\UnusedForeachValueToArrayKeysRectorTest
 */
final class UnusedForeachValueToArrayKeysRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    public function __construct(ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer, BetterNodeFinder $betterNodeFinder, StmtsManipulator $stmtsManipulator)
    {
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->stmtsManipulator = $stmtsManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change foreach with unused $value but only $key, to array_keys()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof Foreach_) {
                continue;
            }
            if (!$stmt->keyVar instanceof Expr) {
                continue;
            }
            if (!$this->nodeTypeResolver->getNativeType($stmt->expr)->isArray()->yes()) {
                continue;
            }
            // special case of nested array items
            if ($stmt->valueVar instanceof Array_) {
                $valueArray = $this->refactorArrayForeachValue($stmt->valueVar, $stmt);
                if ($valueArray instanceof Array_) {
                    $stmt->valueVar = $valueArray;
                }
                // not sure what does this mean :)
                if ($stmt->valueVar->items !== []) {
                    continue;
                }
                $hasChanged = \true;
                $this->removeForeachValueAndUseArrayKeys($stmt, $stmt->keyVar);
                continue;
            }
            if (!$stmt->valueVar instanceof Variable) {
                continue;
            }
            if ($this->isVariableUsedInForeach($stmt->valueVar, $stmt)) {
                continue;
            }
            if ($this->stmtsManipulator->isVariableUsedInNextStmt($stmts, $key + 1, (string) $this->getName($stmt->valueVar))) {
                continue;
            }
            $hasChanged = \true;
            $this->removeForeachValueAndUseArrayKeys($stmt, $stmt->keyVar);
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param int[] $removedKeys
     */
    private function isArrayItemsRemovalWithoutChangingOrder(Array_ $array, array $removedKeys) : bool
    {
        $hasRemovingStarted = \false;
        foreach (\array_keys($array->items) as $key) {
            if (\in_array($key, $removedKeys, \true)) {
                $hasRemovingStarted = \true;
            } elseif ($hasRemovingStarted) {
                // we cannot remove the previous item, and not remove the next one, because that would change the order
                return \false;
            }
        }
        return \true;
    }
    private function refactorArrayForeachValue(Array_ $array, Foreach_ $foreach) : ?Array_
    {
        // only last items can be removed, without changing the order
        $removedKeys = [];
        foreach ($array->items as $key => $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                // only known values can be processes
                return null;
            }
            $value = $arrayItem->value;
            if (!$value instanceof Variable) {
                // only variables can be processed
                return null;
            }
            if ($this->isVariableUsedInForeach($value, $foreach)) {
                continue;
            }
            $removedKeys[] = $key;
        }
        if (!$this->isArrayItemsRemovalWithoutChangingOrder($array, $removedKeys)) {
            return null;
        }
        // clear removed items
        foreach ($removedKeys as $removedKey) {
            unset($array->items[$removedKey]);
        }
        return $array;
    }
    private function isVariableUsedInForeach(Variable $variable, Foreach_ $foreach) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($foreach->stmts, function (Node $node) use($variable) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $variable);
        });
    }
    private function removeForeachValueAndUseArrayKeys(Foreach_ $foreach, Expr $keyVarExpr) : void
    {
        // remove key value
        $foreach->valueVar = $keyVarExpr;
        $foreach->keyVar = null;
        $foreach->expr = $this->nodeFactory->createFuncCall('array_keys', [$foreach->expr]);
    }
}
