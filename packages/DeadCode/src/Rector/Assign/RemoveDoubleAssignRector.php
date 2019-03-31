<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveDoubleAssignRector extends AbstractRector
{
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
        if (! $node->var instanceof Variable && ! $node->var instanceof PropertyFetch) {
            return null;
        }

        $previousExpression = $node->getAttribute(Attribute::PREVIOUS_EXPRESSION);
        if ($previousExpression === null) {
            return null;
        }

        if (! $previousExpression->expr instanceof Assign) {
            return null;
        }

        if (! $this->areNodesEqual($previousExpression->expr, $node)) {
            return null;
        }

        if ($node->expr instanceof FuncCall || $node->expr instanceof StaticCall || $node->expr instanceof MethodCall) {
            return null;
        }

        // skip different method expressions
        if ($node->getAttribute(Attribute::METHOD_NAME) !== $previousExpression->getAttribute(Attribute::METHOD_NAME)) {
            return null;
        }

        // are 2 different methods
        if (! $this->areNodesEqual(
            $node->getAttribute(Attribute::METHOD_NODE),
            $previousExpression->getAttribute(Attribute::METHOD_NODE)
        )) {
            return null;
        }

        if ($this->shouldSkipForDifferentScope($node, $previousExpression)) {
            return null;
        }

        // no calls on right, could hide e.g. array_pop()|array_shift()
        $this->removeNode($node);

        return $node;
    }

    private function shouldSkipDueToForeachOverride(Assign $assign, Node $node): bool
    {
        // is nested in a foreach and the previous expression is not?
        $nodePreviousForeach = $this->betterNodeFinder->findFirstParentInstanceOf($assign, Node\Stmt\Foreach_::class);

        $previousExpressionPreviousForeach = $this->betterNodeFinder->findFirstParentInstanceOf(
            $node,
            Node\Stmt\Foreach_::class
        );

        if ($nodePreviousForeach !== $previousExpressionPreviousForeach) {
            if ($nodePreviousForeach instanceof Node\Stmt\Foreach_ && $assign->var instanceof Variable) {
                // is value changed inside the foreach?

                $variableAssigns = $this->betterNodeFinder->findAssignsOfVariable($nodePreviousForeach, $assign->var);

                // there is probably value override
                return count($variableAssigns) >= 2;
            }
        }

        return false;
    }

    private function shouldSkipForDifferenceParent(Node $firstNode, Node $secondNode): bool
    {
        $firstNodeParent = $this->betterNodeFinder->findFirstParentInstanceOf(
            $firstNode,
            [
                Node\Stmt\Foreach_::class,
                Node\Stmt\If_::class,
                Node\Stmt\While_::class,
                Node\Stmt\Do_::class,
                Node\Stmt\Else_::class,
                Node\Stmt\ElseIf_::class,
            ]
        );

        $secondNodeParent = $this->betterNodeFinder->findFirstParentInstanceOf(
            $secondNode,
            [
                Node\Stmt\Foreach_::class,
                Node\Stmt\If_::class,
                Node\Stmt\While_::class,
                Node\Stmt\Do_::class,
                Node\Stmt\If_::class,
                Node\Stmt\ElseIf_::class,
            ]
        );

        if ($firstNodeParent === null || $secondNodeParent === null) {
            return false;
        }

        if (! $this->areNodesEqual($firstNodeParent, $secondNodeParent)) {
            return true;
        }

        return false;
    }

    private function shouldSkipForDifferentScope(Assign $assign, Node $anotherNode): bool
    {
        if ($this->shouldSkipDueToForeachOverride($assign, $anotherNode)) {
            return true;
        }

        if ($this->shouldSkipForDifferenceParent($assign, $anotherNode)) {
            return true;
        }

        return false;
    }
}
