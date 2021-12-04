<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\ExpectedNameResolver\InflectorSingularResolver;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\DowngradeKeysInListRectorTest
 */
final class DowngradeKeysInListRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\ExpectedNameResolver\InflectorSingularResolver
     */
    private $inflectorSingularResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Naming\ExpectedNameResolver\InflectorSingularResolver $inflectorSingularResolver, \Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->inflectorSingularResolver = $inflectorSingularResolver;
        $this->variableNaming = $variableNaming;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\List_::class, \PhpParser\Node\Expr\Array_::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Extract keys in list to its own variable assignment', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        list("id" => $id1, "name" => $name1) = $data[0];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): void
    {
        $data = [
            ["id" => 1, "name" => 'Tom'],
            ["id" => 2, "name" => 'Fred'],
        ];
        $id1 = $data[0]["id"];
        $name1 = $data[0]["name"];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param List_|Array_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return null;
        }
        $parentExpression = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentExpression instanceof \PhpParser\Node) {
            return null;
        }
        $assignExpressions = $this->processExtractToItsOwnVariable($node, $parent, $parentExpression);
        if ($assignExpressions === []) {
            return null;
        }
        if ($parent instanceof \PhpParser\Node\Expr\Assign) {
            $this->mirrorComments($assignExpressions[0], $parentExpression);
            $this->nodesToAddCollector->addNodesBeforeNode($assignExpressions, $node);
            $this->removeNode($parentExpression);
            return $node;
        }
        if ($parent instanceof \PhpParser\Node\Stmt\Foreach_) {
            $defaultValueVar = $this->inflectorSingularResolver->resolve((string) $this->getName($parent->expr));
            $scope = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            $newValueVar = $this->variableNaming->createCountedValueName($defaultValueVar, $scope);
            $parent->valueVar = new \PhpParser\Node\Expr\Variable($newValueVar);
            $stmts = $parent->stmts;
            if ($stmts === []) {
                $parent->stmts = $assignExpressions;
            } else {
                $this->nodesToAddCollector->addNodesBeforeNode($assignExpressions, $parent->stmts[0]);
            }
            return $parent->valueVar;
        }
        return null;
    }
    /**
     * @return Expression[]
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr\List_ $node
     */
    private function processExtractToItsOwnVariable($node, \PhpParser\Node $parent, \PhpParser\Node $parentExpression) : array
    {
        $items = $node->items;
        $assignExpressions = [];
        foreach ($items as $item) {
            if (!$item instanceof \PhpParser\Node\Expr\ArrayItem) {
                return [];
            }
            /** keyed and not keyed cannot be mixed, return early */
            if (!$item->key instanceof \PhpParser\Node\Expr) {
                return [];
            }
            if ($parentExpression instanceof \PhpParser\Node\Stmt\Expression && $parent instanceof \PhpParser\Node\Expr\Assign && $parent->var === $node) {
                $assignExpressions[] = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($item->value, new \PhpParser\Node\Expr\ArrayDimFetch($parent->expr, $item->key)));
            }
            if (!$parent instanceof \PhpParser\Node\Stmt\Foreach_) {
                continue;
            }
            if ($parent->valueVar !== $node) {
                continue;
            }
            $assignExpressions[] = $this->getExpressionFromForeachValue($parent, $item);
        }
        return $assignExpressions;
    }
    private function getExpressionFromForeachValue(\PhpParser\Node\Stmt\Foreach_ $foreach, \PhpParser\Node\Expr\ArrayItem $arrayItem) : \PhpParser\Node\Stmt\Expression
    {
        $defaultValueVar = $this->inflectorSingularResolver->resolve((string) $this->getName($foreach->expr));
        $scope = $foreach->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $newValueVar = $this->variableNaming->createCountedValueName($defaultValueVar, $scope);
        $assign = new \PhpParser\Node\Expr\Assign($arrayItem->value, new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable($newValueVar), $arrayItem->key));
        return new \PhpParser\Node\Stmt\Expression($assign);
    }
}
