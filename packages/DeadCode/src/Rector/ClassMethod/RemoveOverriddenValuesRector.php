<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use Rector\Context\ContextAnalyzer;
use Rector\DeadCode\Data\VariableNodeUseInfo;
use Rector\DeadCode\FlowOfControlLocator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\RemoveOverriddenValuesRectorTest
 */
final class RemoveOverriddenValuesRector extends AbstractRector
{
    /**
     * @var ContextAnalyzer
     */
    private $contextAnalyzer;

    /**
     * @var FlowOfControlLocator
     */
    private $flowOfControlLocator;

    public function __construct(ContextAnalyzer $contextAnalyzer, FlowOfControlLocator $flowOfControlLocator)
    {
        $this->contextAnalyzer = $contextAnalyzer;
        $this->flowOfControlLocator = $flowOfControlLocator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove initial assigns of overridden values', [
            new CodeSample(
                <<<'PHP'
final class SomeController
{
    public function run()
    {
         $directories = [];
         $possibleDirectories = [];
         $directories = array_filter($possibleDirectories, 'file_exists');
    }
}
PHP
                ,
                <<<'PHP'
final class SomeController
{
    public function run()
    {
         $possibleDirectories = [];
         $directories = array_filter($possibleDirectories, 'file_exists');
    }
}
PHP
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
        $assignedVariablesUse = $this->resolveUsedVariables($node, $assignedVariables);

        $nodesByTypeAndPosition = $this->collectNodesByTypeAndPosition(
            $assignedVariables,
            $assignedVariablesUse,
            $node
        );

        $nodesToRemove = $this->resolveNodesToRemove($assignedVariableNames, $nodesByTypeAndPosition);
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
     * @param Variable[] $assignedVariables
     * @return Variable[]
     */
    private function resolveUsedVariables(Node $node, array $assignedVariables): array
    {
        return $this->betterNodeFinder->find($node, function (Node $node) use ($assignedVariables): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            // is the left assign - not use of one
            if ($parentNode instanceof Assign && ($parentNode->var instanceof Variable && $parentNode->var === $node)) {
                return false;
            }

            // simple variable only
            if ($this->getName($node) === null) {
                return false;
            }

            return $this->isNodeEqual($node, $assignedVariables);
        });
    }

    /**
     * @param Variable[] $assignedVariables
     * @param Variable[] $assignedVariablesUse
     * @return VariableNodeUseInfo[]
     */
    private function collectNodesByTypeAndPosition(
        array $assignedVariables,
        array $assignedVariablesUse,
        FunctionLike $functionLike
    ): array {
        $nodesByTypeAndPosition = [];

        foreach ($assignedVariables as $assignedVariable) {
            /** @var int $startTokenPos */
            $startTokenPos = $assignedVariable->getAttribute(AttributeKey::START_TOKEN_POSITION);

            // not in different scope, than previous one - e.g. if/while/else...
            // get nesting level to $classMethodNode
            /** @var Assign $assignNode */
            $assignNode = $assignedVariable->getAttribute(AttributeKey::PARENT_NODE);
            $nestingHash = $this->flowOfControlLocator->resolveNestingHashFromFunctionLike($functionLike, $assignNode);

            $nodesByTypeAndPosition[] = new VariableNodeUseInfo(
                $startTokenPos,
                $this->getName($assignedVariable),
                VariableNodeUseInfo::TYPE_ASSIGN,
                $assignedVariable,
                $nestingHash
            );
        }

        foreach ($assignedVariablesUse as $assignedVariableUse) {
            /** @var int $startTokenPos */
            $startTokenPos = $assignedVariableUse->getAttribute(AttributeKey::START_TOKEN_POSITION);

            $nodesByTypeAndPosition[] = new VariableNodeUseInfo(
                $startTokenPos,
                $this->getName($assignedVariableUse),
                VariableNodeUseInfo::TYPE_USE,
                $assignedVariableUse
            );
        }

        // sort
        usort(
            $nodesByTypeAndPosition,
            function (
                VariableNodeUseInfo $firstVariableNodeUseInfo,
                VariableNodeUseInfo $secondVariableNodeUseInfo
            ): int {
                return $firstVariableNodeUseInfo->getStartTokenPosition() <=> $secondVariableNodeUseInfo->getStartTokenPosition();
            }
        );

        return $nodesByTypeAndPosition;
    }

    /**
     * @param string[] $assignedVariableNames
     * @param VariableNodeUseInfo[] $nodesByTypeAndPosition
     * @return Node[]
     */
    private function resolveNodesToRemove(array $assignedVariableNames, array $nodesByTypeAndPosition): array
    {
        $nodesToRemove = [];

        foreach ($assignedVariableNames as $assignedVariableName) {
            /** @var VariableNodeUseInfo|null $previousNode */
            $previousNode = null;

            foreach ($nodesByTypeAndPosition as $nodeByTypeAndPosition) {
                if (! $nodeByTypeAndPosition->isName($assignedVariableName)) {
                    continue;
                }

                if ($this->isAssignNodeUsed($previousNode, $nodeByTypeAndPosition)) {
                    // continue

                // instant override → remove
                } elseif ($this->shouldRemoveAssignNode($previousNode, $nodeByTypeAndPosition)) {
                    $nodesToRemove[] = $previousNode->getParentNode();
                }

                $previousNode = $nodeByTypeAndPosition;
            }
        }

        return $nodesToRemove;
    }

    private function isAssignNodeUsed(
        ?VariableNodeUseInfo $previousNode,
        VariableNodeUseInfo $nodeByTypeAndPosition
    ): bool {
        // this node was just used, skip to next one
        return $previousNode !== null && ($previousNode->isType(
            VariableNodeUseInfo::TYPE_ASSIGN
        ) && $nodeByTypeAndPosition->isType(VariableNodeUseInfo::TYPE_USE));
    }

    private function shouldRemoveAssignNode(
        ?VariableNodeUseInfo $previousNode,
        VariableNodeUseInfo $nodeByTypeAndPosition
    ): bool {
        if ($previousNode === null) {
            return false;
        }

        if (! $previousNode->isType(VariableNodeUseInfo::TYPE_ASSIGN) || ! $nodeByTypeAndPosition->isType(
            VariableNodeUseInfo::TYPE_ASSIGN
        )) {
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
