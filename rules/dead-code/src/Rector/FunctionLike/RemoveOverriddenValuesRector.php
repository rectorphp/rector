<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeCollector\NodeByTypeAndPositionCollector;
use Rector\DeadCode\NodeFinder\VariableUseFinder;
use Rector\DeadCode\ValueObject\VariableNodeUse;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\FunctionLike\RemoveOverriddenValuesRector\RemoveOverriddenValuesRectorTest
 */
final class RemoveOverriddenValuesRector extends AbstractRector
{
    /**
     * @var ContextAnalyzer
     */
    private $contextAnalyzer;

    /**
     * @var NodeByTypeAndPositionCollector
     */
    private $nodeByTypeAndPositionCollector;

    /**
     * @var VariableUseFinder
     */
    private $variableUseFinder;

    public function __construct(
        ContextAnalyzer $contextAnalyzer,
        NodeByTypeAndPositionCollector $nodeByTypeAndPositionCollector,
        VariableUseFinder $variableUseFinder
    ) {
        $this->contextAnalyzer = $contextAnalyzer;
        $this->nodeByTypeAndPositionCollector = $nodeByTypeAndPositionCollector;
        $this->variableUseFinder = $variableUseFinder;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove initial assigns of overridden values',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
         $directories = [];
         $possibleDirectories = [];
         $directories = array_filter($possibleDirectories, 'file_exists');
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
         $possibleDirectories = [];
         $directories = array_filter($possibleDirectories, 'file_exists');
    }
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
        return [FunctionLike::class];
    }

    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node): ?Node
    {
        // 1. collect assigns
        $assignedVariables = $this->resolveAssignedVariables($node);
        $assignedVariableNames = $this->getNodeNames($assignedVariables);

        // 2. collect use of those variables
        $assignedVariablesUse = $this->variableUseFinder->resolveUsedVariables($node, $assignedVariables);

        $nodesByTypeAndPosition = $this->nodeByTypeAndPositionCollector->collectNodesByTypeAndPosition(
            $assignedVariables,
            $assignedVariablesUse,
            $node
        );

        $nodesToRemove = $this->resolveNodesToRemove($assignedVariableNames, $nodesByTypeAndPosition);
        if ($nodesToRemove === []) {
            return null;
        }

        $this->removeNodes($nodesToRemove);

        return $node;
    }

    /**
     * @return Variable[]
     */
    private function resolveAssignedVariables(FunctionLike $functionLike): array
    {
        return $this->betterNodeFinder->find($functionLike, function (Node $node): bool {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parentNode instanceof Assign) {
                return false;
            }

            if (! $node instanceof Variable) {
                return false;
            }

            // skin in if
            if ($this->contextAnalyzer->isInIf($node)) {
                return false;
            }

            // is variable on the left
            /** @var Assign $assignNode */
            $assignNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($assignNode->var !== $node) {
                return false;
            }
            // simple variable only
            return is_string($node->name);
        });
    }

    /**
     * @param Node[] $nodes
     * @return string[]
     */
    private function getNodeNames(array $nodes): array
    {
        $nodeNames = [];
        foreach ($nodes as $node) {
            $nodeName = $this->getName($node);
            if ($nodeName) {
                $nodeNames[] = $nodeName;
            }
        }

        return array_unique($nodeNames);
    }

    /**
     * @param string[] $assignedVariableNames
     * @param VariableNodeUse[] $nodesByTypeAndPosition
     * @return Node[]
     */
    private function resolveNodesToRemove(array $assignedVariableNames, array $nodesByTypeAndPosition): array
    {
        $nodesToRemove = [];

        foreach ($assignedVariableNames as $assignedVariableName) {
            $previousNode = null;

            foreach ($nodesByTypeAndPosition as $nodeByTypeAndPosition) {
                $variableNode = $nodeByTypeAndPosition->getVariableNode();
                $comments = $variableNode->getAttribute(AttributeKey::COMMENTS);

                if ($comments !== null) {
                    continue;
                }

                if (! $nodeByTypeAndPosition->isName($assignedVariableName)) {
                    continue;
                }

                if ($this->isAssignNodeUsed($previousNode, $nodeByTypeAndPosition)) {
                    // continue
                    // instant override → remove
                } elseif ($this->shouldRemoveAssignNode($previousNode, $nodeByTypeAndPosition)) {
                    /** @var VariableNodeUse $previousNode */
                    $nodesToRemove[] = $previousNode->getParentNode();
                }

                $previousNode = $nodeByTypeAndPosition;
            }
        }

        return $nodesToRemove;
    }

    private function isAssignNodeUsed(
        ?VariableNodeUse $previousNode,
        VariableNodeUse $nodeByTypeAndPosition
    ): bool {
        // this node was just used, skip to next one
        if (! $previousNode instanceof VariableNodeUse) {
            return false;
        }

        if (! $previousNode->isType(VariableNodeUse::TYPE_ASSIGN)) {
            return false;
        }

        return $nodeByTypeAndPosition->isType(VariableNodeUse::TYPE_USE);
    }

    private function shouldRemoveAssignNode(
        ?VariableNodeUse $previousNode,
        VariableNodeUse $nodeByTypeAndPosition
    ): bool {
        if ($previousNode === null) {
            return false;
        }

        if (! $previousNode->isType(VariableNodeUse::TYPE_ASSIGN)) {
            return false;
        }

        if (! $nodeByTypeAndPosition->isType(VariableNodeUse::TYPE_ASSIGN)) {
            return false;
        }

        // check the nesting level, e.g. call in if/while/else etc.
        if ($previousNode->getNestingHash() !== $nodeByTypeAndPosition->getNestingHash()) {
            return false;
        }

        // check previous node doesn't contain the node on the right, e.g.
        // $someNode = 1;
        // $someNode = $someNode ?: 1;
        /** @var Assign $assignNode */
        $assignNode = $nodeByTypeAndPosition->getParentNode();

        $isVariableAssigned = (bool) $this->betterNodeFinder->findFirst($assignNode->expr, function (Node $node) use (
            $nodeByTypeAndPosition
        ): bool {
            return $this->areNodesEqual($node, $nodeByTypeAndPosition->getVariableNode());
        });

        return ! $isVariableAssigned;
    }
}
