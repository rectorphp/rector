<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\For_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\For_;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector\ForRepeatedCountToOwnVariableRectorTest
 */
final class ForRepeatedCountToOwnVariableRector extends \Rector\Core\Rector\AbstractRector
{
    /**
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
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $countInCond = null;
        $variableName = null;
        $forScope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $this->traverseNodesWithCallable($node->cond, function (\PhpParser\Node $node) use(&$countInCond, &$variableName, $forScope) : ?Variable {
            if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
                return null;
            }
            if (!$this->isName($node, 'count')) {
                return null;
            }
            $countInCond = $node;
            $variableName = $this->variableNaming->resolveFromFuncCallFirstArgumentWithSuffix($node, 'Count', 'itemsCount', $forScope);
            return new \PhpParser\Node\Expr\Variable($variableName);
        });
        if ($countInCond === null) {
            return null;
        }
        if ($variableName === null) {
            return null;
        }
        $countAssign = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable($variableName), $countInCond);
        $this->nodesToAddCollector->addNodeBeforeNode($countAssign, $node);
        return $node;
    }
}
