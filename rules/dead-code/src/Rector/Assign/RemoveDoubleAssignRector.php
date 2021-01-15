<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\RemoveDoubleAssignRectorTest
 */
final class RemoveDoubleAssignRector extends AbstractRector
{
    /**
     * @var ScopeNestingComparator
     */
    private $scopeNestingComparator;

    public function __construct(ScopeNestingComparator $scopeNestingComparator)
    {
        $this->scopeNestingComparator = $scopeNestingComparator;
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
     * @return string[]
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

        if (! $this->areNodesEqual($previousStatement->expr->var, $node->var)) {
            return null;
        }

        if ($this->isCall($previousStatement->expr->expr)) {
            return null;
        }

        if ($this->shouldSkipForDifferentScope($node, $previousStatement)) {
            return null;
        }

        if ($this->isSelfReferencing($node)) {
            return null;
        }

        // no calls on right, could hide e.g. array_pop()|array_shift()
        $this->removeNode($previousStatement);

        return $node;
    }

    private function isCall(Expr $expr): bool
    {
        return StaticInstanceOf::isOneOf($expr, [FuncCall::class, StaticCall::class, MethodCall::class]);
    }

    private function shouldSkipForDifferentScope(Assign $assign, Expression $expression): bool
    {
        if (! $this->areInSameClassMethod($assign, $expression)) {
            return true;
        }

        return ! $this->scopeNestingComparator->areScopeNestingEqual($assign, $expression);
    }

    private function isSelfReferencing(Assign $assign): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($assign->expr, function (Node $subNode) use ($assign): bool {
            return $this->areNodesEqual($assign->var, $subNode);
        });
    }

    private function areInSameClassMethod(Assign $assign, Expression $previousExpression): bool
    {
        return $this->areNodesEqual(
            $assign->getAttribute(AttributeKey::METHOD_NODE),
            $previousExpression->getAttribute(AttributeKey::METHOD_NODE)
        );
    }
}
