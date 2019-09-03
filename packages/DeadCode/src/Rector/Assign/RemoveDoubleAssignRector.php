<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\RemoveDoubleAssignRectorTest
 */
final class RemoveDoubleAssignRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const CONTROL_STRUCTURE_NODES = [
        Foreach_::class,
        If_::class,
        While_::class,
        Do_::class,
        Else_::class,
        ElseIf_::class,
        Catch_::class,
        Case_::class,
    ];

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

        $previousExpression = $node->getAttribute(AttributeKey::PREVIOUS_EXPRESSION);
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
        $nodePreviousForeach = $this->betterNodeFinder->findFirstParentInstanceOf($assign, Foreach_::class);

        $previousExpressionPreviousForeach = $this->betterNodeFinder->findFirstParentInstanceOf(
            $node,
            Foreach_::class
        );
        return $nodePreviousForeach !== $previousExpressionPreviousForeach;
    }

    private function shouldSkipForDifferenceParent(Node $firstNode, Node $secondNode): bool
    {
        $firstNodeParent = $this->findParentControlStructure($firstNode);
        $secondNodeParent = $this->findParentControlStructure($secondNode);

        if ($firstNodeParent === null || $secondNodeParent === null) {
            return false;
        }

        return ! $this->areNodesEqual($firstNodeParent, $secondNodeParent);
    }

    private function shouldSkipForDifferentScope(Assign $assign, Node $anotherNode): bool
    {
        if (! $this->areInSameClassMethod($assign, $anotherNode)) {
            return true;
        }

        if ($this->shouldSkipDueToForeachOverride($assign, $anotherNode)) {
            return true;
        }

        return $this->shouldSkipForDifferenceParent($assign, $anotherNode);
    }

    private function findParentControlStructure(Node $node): ?Node
    {
        return $this->betterNodeFinder->findFirstParentInstanceOf($node, self::CONTROL_STRUCTURE_NODES);
    }

    private function areInSameClassMethod(Node $node, Node $previousExpression): bool
    {
        return $this->areNodesEqual(
            $node->getAttribute(AttributeKey::METHOD_NODE),
            $previousExpression->getAttribute(AttributeKey::METHOD_NODE)
        );
    }
}
