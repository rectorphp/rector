<?php

declare (strict_types=1);
namespace Rector\Naming\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Type\ThisType;
use Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\ExpectedNameResolver\InflectorSingularResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\RenameForeachValueVariableToMatchExprVariableRectorTest
 */
final class RenameForeachValueVariableToMatchExprVariableRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Naming\ExpectedNameResolver\InflectorSingularResolver
     */
    private $inflectorSingularResolver;
    /**
     * @var \Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer
     */
    private $foreachAnalyzer;
    public function __construct(\Rector\Naming\ExpectedNameResolver\InflectorSingularResolver $inflectorSingularResolver, \Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer $foreachAnalyzer)
    {
        $this->inflectorSingularResolver = $inflectorSingularResolver;
        $this->foreachAnalyzer = $foreachAnalyzer;
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
        if (!$node->expr instanceof \PhpParser\Node\Expr\Variable && !$node->expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        if ($this->isNotThisTypePropertyFetch($node->expr)) {
            return null;
        }
        $exprName = $this->getName($node->expr);
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
    private function isNotThisTypePropertyFetch(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $variableType = $this->getType($expr->var);
            return !$variableType instanceof \PHPStan\Type\ThisType;
        }
        return \false;
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
