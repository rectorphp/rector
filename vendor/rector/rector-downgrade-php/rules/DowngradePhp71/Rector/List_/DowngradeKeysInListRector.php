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
use PhpParser\Node\Stmt;
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
    public function __construct(InflectorSingularResolver $inflectorSingularResolver, VariableNaming $variableNaming)
    {
        $this->inflectorSingularResolver = $inflectorSingularResolver;
        $this->variableNaming = $variableNaming;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class, Foreach_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Extract keys in list to its own variable assignment', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $data): void
    {
        list("id" => $id1, "name" => $name1) = $data[0];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): void
    {
        $id1 = $data[0]["id"];
        $name1 = $data[0]["name"];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Expression|Foreach_ $node
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Expression) {
            if ($node->expr instanceof Assign) {
                return $this->refactorAssignExpression($node);
            }
            return null;
        }
        return $this->refactorForeach($node);
    }
    /**
     * @return Stmt[]
     * @param \PhpParser\Node\Expr\List_|\PhpParser\Node\Expr\Array_ $listOrArray
     */
    private function refactorArrayOrList($listOrArray, Assign $assign) : array
    {
        $items = $listOrArray->items;
        $assignStmts = [];
        foreach ($items as $item) {
            if (!$item instanceof ArrayItem) {
                return [];
            }
            // keyed and not keyed cannot be mixed, return early
            if (!$item->key instanceof Expr) {
                return [];
            }
            $assignStmts[] = new Expression(new Assign($item->value, new ArrayDimFetch($assign->expr, $item->key)));
        }
        return $assignStmts;
    }
    /**
     * @return Stmt[]|null
     * @param \PhpParser\Node\Expr\List_|\PhpParser\Node\Expr\Array_ $listOrArray
     */
    private function refactorForeachToOwnVariables($listOrArray, Foreach_ $foreach) : ?array
    {
        $items = $listOrArray->items;
        $assignStmts = [];
        foreach ($items as $item) {
            if (!$item instanceof ArrayItem) {
                return null;
            }
            // keyed and not keyed cannot be mixed, return early
            if (!$item->key instanceof Expr) {
                return null;
            }
            $assignStmts[] = $this->createExpressionFromForeachValue($foreach, $item);
        }
        return $assignStmts;
    }
    private function createExpressionFromForeachValue(Foreach_ $foreach, ArrayItem $arrayItem) : Expression
    {
        $defaultValueVar = $this->inflectorSingularResolver->resolve((string) $this->getName($foreach->expr));
        $scope = $foreach->getAttribute(AttributeKey::SCOPE);
        $newValueVar = $this->variableNaming->createCountedValueName($defaultValueVar, $scope);
        $assign = new Assign($arrayItem->value, new ArrayDimFetch(new Variable($newValueVar), $arrayItem->key));
        return new Expression($assign);
    }
    /**
     * @param Expression<Assign> $expression
     * @return Stmt[]|null
     */
    private function refactorAssignExpression(Expression $expression) : ?array
    {
        /** @var Assign $assign */
        $assign = $expression->expr;
        if (!$assign->var instanceof List_ && !$assign->var instanceof Array_) {
            return null;
        }
        $assignedArrayOrList = $assign->var;
        $assignExpressions = $this->refactorArrayOrList($assignedArrayOrList, $assign);
        if ($assignExpressions === []) {
            return null;
        }
        $this->mirrorComments($assignExpressions[0], $expression);
        return $assignExpressions;
    }
    private function refactorForeach(Foreach_ $foreach) : ?Foreach_
    {
        if (!$foreach->valueVar instanceof List_ && !$foreach->valueVar instanceof Array_) {
            return null;
        }
        $assignedArrayOrList = $foreach->valueVar;
        $defaultValueVar = $this->inflectorSingularResolver->resolve((string) $this->getName($foreach->expr));
        $scope = $foreach->getAttribute(AttributeKey::SCOPE);
        $newValueVar = $this->variableNaming->createCountedValueName($defaultValueVar, $scope);
        $foreach->valueVar = new Variable($newValueVar);
        $assignExpressions = $this->refactorForeachToOwnVariables($assignedArrayOrList, $foreach);
        if ($assignExpressions === null) {
            return null;
        }
        $foreach->stmts = \array_merge($assignExpressions, $foreach->stmts);
        return $foreach;
    }
}
