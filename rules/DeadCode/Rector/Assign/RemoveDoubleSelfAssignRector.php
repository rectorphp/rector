<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleSelfAssignRector\RemoveDoubleSelfAssignRectorTest
 */
final class RemoveDoubleSelfAssignRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove duplicated self assign of the same variable', [new CodeSample('$validator = $validator = Validation::createValidator();', '$validator = Validation::createValidator();')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->var instanceof Variable) {
            return null;
        }
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $innerAssign = $node->expr;
        if (!$innerAssign->var instanceof Variable) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($node->var, $innerAssign->var)) {
            return null;
        }
        return $innerAssign;
    }
}
