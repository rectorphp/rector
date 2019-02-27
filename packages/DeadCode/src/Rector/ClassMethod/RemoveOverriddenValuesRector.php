<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use Rector\DeadCode\Rector\ClassMethod\Data\VariableNodeUseInfo;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveOverriddenValuesRector extends AbstractRector
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove initial assigns of overridden values', [
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
     * @param ClassMethod $node
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
        return $this->betterNodeFinder->find($functionLike, function (Node $node) {
            if (! $node->getAttribute(Attribute::PARENT_NODE) instanceof Assign) {
                return false;
            }

            if (! $node instanceof Variable) {
                return false;
            }

            // is variable on the left
            /** @var Assign $assignNode */
            $assignNode = $node->getAttribute(Attribute::PARENT_NODE);
            if ($assignNode->var !== $node) {
                return false;
            }

            // simple variable only
            if (is_string($node->name)) {
                return true;
            }

            return false;
        });
    }

    /**
     * @param Variable[] $assignedVariables
     * @return Variable[]
     */
    private function resolveUsedVariables(Node $node, array $assignedVariables): array
    {
        return $this->betterNodeFinder->find($node, function (Node $node) use ($assignedVariables) {
            if (! $node instanceof Variable) {
                return false;
            }

            $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
            if ($parentNode instanceof Assign) {
                // is the left assign - not use of one
                if ($parentNode->var instanceof Variable && $parentNode->var === $node) {
                    return false;
                }
            }

            // simple variable only
            if ($this->getName($node) === null) {
                return false;
            }

            return $this->isNodeEqual($node, $assignedVariables);
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
            $startTokenPos = $assignedVariable->getAttribute(Attribute::START_TOKEN_POSITION);

            // not in different scope, than previous one - e.g. if/while/else...
            // get nesting level to $classMethodNode

            /** @var Assign $assignNode */
            $assignNode = $assignedVariable->getAttribute(Attribute::PARENT_NODE);
            $nestingHash = $this->resolveNestingHashFromClassMethod($functionLike, $assignNode);

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
            $startTokenPos = $assignedVariableUse->getAttribute(Attribute::START_TOKEN_POSITION);

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
            function (VariableNodeUseInfo $firstVariableNodeUseInfo, VariableNodeUseInfo $secondVariableNodeUseInfo) {
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
        ) {
            if ($this->areNodesEqual($node, $nodeByTypeAndPosition->getVariableNode())) {
                return true;
            }

            return false;
        });

        return ! $isVariableAssigned;
    }

    private function resolveNestingHashFromClassMethod(FunctionLike $functionLike, Assign $assign): string
    {
        $nestingHash = '_';
        $parentNode = $assign;
        while ($parentNode = $parentNode->getAttribute(Attribute::PARENT_NODE)) {
            if ($parentNode instanceof Expression) {
                continue;
            }

            $nestingHash .= spl_object_hash($parentNode);
            if ($functionLike === $parentNode) {
                return $nestingHash;
            }
        }

        return $nestingHash;
    }

    private function isAssignNodeUsed(
        ?VariableNodeUseInfo $previousNode,
        VariableNodeUseInfo $nodeByTypeAndPosition
    ): bool {
        // this node was just used, skip to next one
        if ($previousNode !== null) {
            if ($previousNode->isType(VariableNodeUseInfo::TYPE_ASSIGN) && $nodeByTypeAndPosition->isType(
                VariableNodeUseInfo::TYPE_USE
            )) {
                return true;
            }
        }

        return false;
    }
}
