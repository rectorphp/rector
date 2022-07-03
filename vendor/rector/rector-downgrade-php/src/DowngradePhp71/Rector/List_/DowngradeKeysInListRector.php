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
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp71\Rector\List_\DowngradeKeysInListRector\DowngradeKeysInListRectorTest
 */
final class DowngradeKeysInListRector extends AbstractRector
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
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(InflectorSingularResolver $inflectorSingularResolver, VariableNaming $variableNaming, NodesToAddCollector $nodesToAddCollector)
    {
        $this->inflectorSingularResolver = $inflectorSingularResolver;
        $this->variableNaming = $variableNaming;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [List_::class, Array_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Extract keys in list to its own variable assignment', [new CodeSample(<<<'CODE_SAMPLE'
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
    public function refactor(Node $node) : ?Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof Node) {
            return null;
        }
        $parentExpression = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentExpression instanceof Node) {
            return null;
        }
        $assignExpressions = $this->processExtractToItsOwnVariable($node, $parent, $parentExpression);
        if ($assignExpressions === []) {
            return null;
        }
        if ($parent instanceof Assign) {
            $this->mirrorComments($assignExpressions[0], $parentExpression);
            $this->nodesToAddCollector->addNodesBeforeNode($assignExpressions, $node);
            $this->removeNode($parentExpression);
            return $node;
        }
        if ($parent instanceof Foreach_) {
            $defaultValueVar = $this->inflectorSingularResolver->resolve((string) $this->getName($parent->expr));
            $scope = $parent->getAttribute(AttributeKey::SCOPE);
            $newValueVar = $this->variableNaming->createCountedValueName($defaultValueVar, $scope);
            $parent->valueVar = new Variable($newValueVar);
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
     * @param \PhpParser\Node\Expr\List_|\PhpParser\Node\Expr\Array_ $node
     */
    private function processExtractToItsOwnVariable($node, Node $parent, Node $parentExpression) : array
    {
        $items = $node->items;
        $assignExpressions = [];
        foreach ($items as $item) {
            if (!$item instanceof ArrayItem) {
                return [];
            }
            /** keyed and not keyed cannot be mixed, return early */
            if (!$item->key instanceof Expr) {
                return [];
            }
            if ($parentExpression instanceof Expression && $parent instanceof Assign && $parent->var === $node) {
                $assignExpressions[] = new Expression(new Assign($item->value, new ArrayDimFetch($parent->expr, $item->key)));
            }
            if (!$parent instanceof Foreach_) {
                continue;
            }
            if ($parent->valueVar !== $node) {
                continue;
            }
            $assignExpressions[] = $this->getExpressionFromForeachValue($parent, $item);
        }
        return $assignExpressions;
    }
    private function getExpressionFromForeachValue(Foreach_ $foreach, ArrayItem $arrayItem) : Expression
    {
        $defaultValueVar = $this->inflectorSingularResolver->resolve((string) $this->getName($foreach->expr));
        $scope = $foreach->getAttribute(AttributeKey::SCOPE);
        $newValueVar = $this->variableNaming->createCountedValueName($defaultValueVar, $scope);
        $assign = new Assign($arrayItem->value, new ArrayDimFetch(new Variable($newValueVar), $arrayItem->key));
        return new Expression($assign);
    }
}
