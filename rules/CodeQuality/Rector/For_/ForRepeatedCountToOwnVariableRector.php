<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\For_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Naming\Naming\VariableNaming;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector\ForRepeatedCountToOwnVariableRectorTest
 */
final class ForRepeatedCountToOwnVariableRector extends \Rector\Core\Rector\AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change count() in for function to own variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        for ($i = 5; $i <= count($items); $i++) {
            echo $items[$i];
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $itemsCount = count($items);
        for ($i = 5; $i <= $itemsCount; $i++) {
            echo $items[$i];
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
        return [\PhpParser\Node\Stmt\For_::class];
    }
    /**
     * @param For_ $node
     * @return Stmt[]|null
     */
    public function refactorWithScope(\PhpParser\Node $node, \PHPStan\Analyser\Scope $scope) : ?array
    {
        $countInCond = null;
        $variableName = null;
        $this->traverseNodesWithCallable($node->cond, function (\PhpParser\Node $node) use(&$countInCond, &$variableName, $scope) : ?Variable {
            if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
                return null;
            }
            if (!$this->isName($node, 'count')) {
                return null;
            }
            $countInCond = $node;
            $variableName = $this->variableNaming->resolveFromFuncCallFirstArgumentWithSuffix($node, 'Count', 'itemsCount', $scope);
            return new \PhpParser\Node\Expr\Variable($variableName);
        });
        if ($countInCond === null) {
            return null;
        }
        if ($variableName === null) {
            return null;
        }
        $countAssign = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable($variableName), $countInCond);
        return [new \PhpParser\Node\Stmt\Expression($countAssign), $node];
    }
}
