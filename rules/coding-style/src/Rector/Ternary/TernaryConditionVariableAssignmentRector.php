<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Ternary\TernaryConditionVariableAssignmentRector\TernaryConditionVariableAssignmentRectorTest
 */
final class TernaryConditionVariableAssignmentRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Assign outcome of ternary condition to variable, where applicable',
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
function ternary($value)
{
    $value ? $a = 1 : $a = 0;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
function ternary($value)
{
    $a = $value ? 1 : 0;
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        $nodeIf = $node->if;
        $nodeElse = $node->else;

        if (! $nodeIf instanceof Assign || ! $nodeElse instanceof Assign) {
            return null;
        }

        $nodeIfVar = $nodeIf->var;
        $nodeElseVar = $nodeElse->var;

        if (! $nodeIfVar instanceof Variable || ! $nodeElseVar instanceof Variable) {
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
