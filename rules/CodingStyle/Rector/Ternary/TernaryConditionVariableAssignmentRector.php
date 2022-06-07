<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector\TernaryConditionVariableAssignmentRectorTest
 */
final class TernaryConditionVariableAssignmentRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Assign outcome of ternary condition to variable, where applicable', [new CodeSample(<<<'CODE_SAMPLE'
function ternary($value)
{
    $value ? $a = 1 : $a = 0;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function ternary($value)
{
    $a = $value ? 1 : 0;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        $nodeIf = $node->if;
        $nodeElse = $node->else;
        if (!$nodeIf instanceof Assign) {
            return null;
        }
        if (!$nodeElse instanceof Assign) {
            return null;
        }
        $nodeIfVar = $nodeIf->var;
        $nodeElseVar = $nodeElse->var;
        if (!$nodeIfVar instanceof Variable) {
            return null;
        }
        if (!$nodeElseVar instanceof Variable) {
            return null;
        }
        if ($nodeIfVar->name !== $nodeElseVar->name) {
            return null;
        }
        $previousNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if ($previousNode !== null) {
            return null;
        }
        $node->if = $nodeIf->expr;
        $node->else = $nodeElse->expr;
        $variable = new Variable($nodeIfVar->name);
        return new Assign($variable, $node);
    }
}
