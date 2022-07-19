<?php

declare (strict_types=1);
namespace Rector\Naming\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Type\ThisType;
use Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\ExpectedNameResolver\InflectorSingularResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\RenameForeachValueVariableToMatchExprVariableRectorTest
 */
final class RenameForeachValueVariableToMatchExprVariableRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\ExpectedNameResolver\InflectorSingularResolver
     */
    private $inflectorSingularResolver;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer
     */
    private $foreachAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(InflectorSingularResolver $inflectorSingularResolver, ForeachAnalyzer $foreachAnalyzer, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->inflectorSingularResolver = $inflectorSingularResolver;
        $this->foreachAnalyzer = $foreachAnalyzer;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Renames value variable name in foreach loop to match expression variable', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $array = [];
        foreach ($variables as $property) {
            $array[] = $property;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $array = [];
        foreach ($variables as $variable) {
            $array[] = $variable;
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
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $isPropertyFetch = $this->propertyFetchAnalyzer->isPropertyFetch($node->expr);
        if (!$node->expr instanceof Variable && !$isPropertyFetch) {
            return null;
        }
        /** @var Variable|PropertyFetch|StaticPropertyFetch $expr */
        $expr = $node->expr;
        if ($this->isNotCurrentClassLikePropertyFetch($expr, $isPropertyFetch)) {
            return null;
        }
        $exprName = $this->getName($expr);
        if ($exprName === null) {
            return null;
        }
        if ($node->keyVar instanceof Node) {
            return null;
        }
        $valueVarName = $this->getName($node->valueVar);
        if ($valueVarName === null) {
            return null;
        }
        $singularValueVarName = $this->inflectorSingularResolver->resolve($exprName);
        if ($singularValueVarName === $exprName) {
            return null;
        }
        if ($singularValueVarName === $valueVarName) {
            return null;
        }
        if ($this->foreachAnalyzer->isValueVarUsed($node, $singularValueVarName)) {
            return null;
        }
        return $this->processRename($node, $valueVarName, $singularValueVarName);
    }
    /**
     * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr\Variable $expr
     */
    private function isNotCurrentClassLikePropertyFetch($expr, bool $isPropertyFetch) : bool
    {
        if (!$isPropertyFetch) {
            return \false;
        }
        /** @var PropertyFetch|StaticPropertyFetch $expr */
        $variableType = $expr instanceof PropertyFetch ? $this->nodeTypeResolver->getType($expr->var) : $this->nodeTypeResolver->getType($expr->class);
        if ($variableType instanceof FullyQualifiedObjectType) {
            $currentClassLike = $this->betterNodeFinder->findParentType($expr, ClassLike::class);
            if ($currentClassLike instanceof ClassLike) {
                return !$this->nodeNameResolver->isName($currentClassLike, $variableType->getClassName());
            }
        }
        return !$variableType instanceof ThisType;
    }
    private function processRename(Foreach_ $foreach, string $valueVarName, string $singularValueVarName) : Foreach_
    {
        $foreach->valueVar = new Variable($singularValueVarName);
        $this->traverseNodesWithCallable($foreach->stmts, function (Node $node) use($singularValueVarName, $valueVarName) : ?Variable {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->isName($node, $valueVarName)) {
                return null;
            }
            return new Variable($singularValueVarName);
        });
        return $foreach;
    }
}
