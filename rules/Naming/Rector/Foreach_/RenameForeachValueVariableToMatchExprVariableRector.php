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
final class RenameForeachValueVariableToMatchExprVariableRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Naming\ExpectedNameResolver\InflectorSingularResolver $inflectorSingularResolver, \Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer $foreachAnalyzer, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->inflectorSingularResolver = $inflectorSingularResolver;
        $this->foreachAnalyzer = $foreachAnalyzer;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Renames value variable name in foreach loop to match expression variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $isPropertyFetch = $this->propertyFetchAnalyzer->isPropertyFetch($node->expr);
        if (!$node->expr instanceof \PhpParser\Node\Expr\Variable && !$isPropertyFetch) {
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
        if ($node->keyVar instanceof \PhpParser\Node) {
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
        $variableType = $expr instanceof \PhpParser\Node\Expr\PropertyFetch ? $this->nodeTypeResolver->getType($expr->var) : $this->nodeTypeResolver->getType($expr->class);
        if ($variableType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
            $currentClassLike = $this->betterNodeFinder->findParentType($expr, \PhpParser\Node\Stmt\ClassLike::class);
            if ($currentClassLike instanceof \PhpParser\Node\Stmt\ClassLike) {
                return !$this->nodeNameResolver->isName($currentClassLike, $variableType->getClassName());
            }
        }
        return !$variableType instanceof \PHPStan\Type\ThisType;
    }
    private function processRename(\PhpParser\Node\Stmt\Foreach_ $foreach, string $valueVarName, string $singularValueVarName) : \PhpParser\Node\Stmt\Foreach_
    {
        $foreach->valueVar = new \PhpParser\Node\Expr\Variable($singularValueVarName);
        $this->traverseNodesWithCallable($foreach->stmts, function (\PhpParser\Node $node) use($singularValueVarName, $valueVarName) : ?Variable {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if (!$this->isName($node, $valueVarName)) {
                return null;
            }
            return new \PhpParser\Node\Expr\Variable($singularValueVarName);
        });
        return $foreach;
    }
}
