<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeNestingScope\ParentFinder;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleAssignRector\RemoveDoubleAssignRectorTest
 */
final class RemoveDoubleAssignRector extends AbstractRector
{
    public function __construct(
        private ScopeNestingComparator $scopeNestingComparator,
        private SideEffectNodeDetector $sideEffectNodeDetector,
        private ParentFinder $parentFinder
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Simplify useless double assigns', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$value = 1;
$value = 1;
CODE_SAMPLE
                ,
                '$value = 1;'
            ),
        ]);
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
        if (! $node->var instanceof Variable && ! $node->var instanceof PropertyFetch && ! $node->var instanceof StaticPropertyFetch) {
            return null;
        }

        $previousStatement = $node->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        if (! $previousStatement instanceof Expression) {
            return null;
        }

        if (! $previousStatement->expr instanceof Assign) {
            return null;
        }

        if (! $this->nodeComparator->areNodesEqual($previousStatement->expr->var, $node->var)) {
            return null;
        }

        // early check self referencing, ensure that variable not re-used
        if ($this->isSelfReferencing($node)) {
            return null;
        }

        // detect call expression has side effect
        if ($this->sideEffectNodeDetector->detectCallExpr($previousStatement->expr->expr)) {
            return null;
        }

        // check scoping variable
        if ($this->shouldSkipForDifferentScope($node, $previousStatement)) {
            return null;
        }

        // no calls on right, could hide e.g. array_pop()|array_shift()
        $this->removeNode($previousStatement);

        return $node;
    }

    private function shouldSkipForDifferentScope(Assign $assign, Expression $expression): bool
    {
        if (! $this->areInSameClassMethod($assign, $expression)) {
            return true;
        }

        if (! $this->scopeNestingComparator->areScopeNestingEqual($assign, $expression)) {
            return true;
        }

        return (bool) $this->parentFinder->findByTypes($assign, [Ternary::class, Coalesce::class]);
    }

    private function isSelfReferencing(Assign $assign): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $assign->expr,
            fn (Node $subNode): bool => $this->nodeComparator->areNodesEqual($assign->var, $subNode)
        );
    }

    private function areInSameClassMethod(Assign $assign, Expression $previousExpression): bool
    {
        return $this->nodeComparator->areNodesEqual(
            $assign->getAttribute(AttributeKey::METHOD_NODE),
            $previousExpression->getAttribute(AttributeKey::METHOD_NODE)
        );
    }
}
