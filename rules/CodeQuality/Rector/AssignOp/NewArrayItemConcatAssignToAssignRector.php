<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\AssignOp;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Concat;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\AssignOp\NewArrayItemConcatAssignToAssignRector\NewArrayItemConcatAssignToAssignRectorTest
 */
final class NewArrayItemConcatAssignToAssignRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change concat assign on a new array item to plain assign, as the new item is always null', [new CodeSample(<<<'CODE_SAMPLE'
$values[] .= $name;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$values[] = $name;
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Concat::class];
    }
    /**
     * @param Concat $node
     */
    public function refactor(Node $node): ?Assign
    {
        if (!$node->var instanceof ArrayDimFetch) {
            return null;
        }
        if ($node->var->dim instanceof Node) {
            return null;
        }
        return new Assign($node->var, $node->expr);
    }
}
