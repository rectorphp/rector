<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify useless double assigns', [
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

        if ($node->expr instanceof FuncCall || $node->expr instanceof StaticCall || $node->expr instanceof MethodCall) {
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

    private function shouldSkipForDifferentScope(Assign $assign, Node $anotherNode): bool
    {
        if (! $this->areInSameClassMethod($assign, $anotherNode)) {
            return true;
        }

        return ! $this->scopeNestingComparator->areScopeNestingEqual($assign, $anotherNode);
    }

    private function isSelfReferencing(Assign $assign): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($assign->expr, function (Node $subNode) use ($assign): bool {
            return $this->areNodesEqual($assign->var, $subNode);
        });
    }

    private function areInSameClassMethod(Node $node, Node $previousExpression): bool
    {
        return $this->areNodesEqual(
            $node->getAttribute(AttributeKey::METHOD_NODE),
            $previousExpression->getAttribute(AttributeKey::METHOD_NODE)
        );
    }
}
