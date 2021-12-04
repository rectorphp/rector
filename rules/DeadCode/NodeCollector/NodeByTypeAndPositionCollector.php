<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeCollector;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use Rector\DeadCode\ValueObject\VariableNodeUse;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNestingScope\FlowOfControlLocator;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NodeByTypeAndPositionCollector
{
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\FlowOfControlLocator
     */
    private $flowOfControlLocator;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNestingScope\FlowOfControlLocator $flowOfControlLocator, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->flowOfControlLocator = $flowOfControlLocator;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param Variable[] $assignedVariables
     * @param Variable[] $assignedVariablesUse
     * @return VariableNodeUse[]
     */
    public function collectNodesByTypeAndPosition(array $assignedVariables, array $assignedVariablesUse, \PhpParser\Node\FunctionLike $functionLike) : array
    {
        $nodesByTypeAndPosition = [];
        foreach ($assignedVariables as $assignedVariable) {
            $startTokenPos = $assignedVariable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::START_TOKEN_POSITION);
            if ($startTokenPos === null) {
                continue;
            }
            // not in different scope, than previous one - e.g. if/while/else...
            // get nesting level to $classMethodNode
            /** @var Assign $assign */
            $assign = $assignedVariable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            $nestingHash = $this->flowOfControlLocator->resolveNestingHashFromFunctionLike($functionLike, $assign);
            /** @var string $variableName */
            $variableName = $this->nodeNameResolver->getName($assignedVariable);
            $nodesByTypeAndPosition[] = new \Rector\DeadCode\ValueObject\VariableNodeUse($startTokenPos, $variableName, \Rector\DeadCode\ValueObject\VariableNodeUse::TYPE_ASSIGN, $assignedVariable, $nestingHash);
        }
        foreach ($assignedVariablesUse as $assignedVariableUse) {
            /** @var int|null $startTokenPos */
            $startTokenPos = $assignedVariableUse->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::START_TOKEN_POSITION);
            if ($startTokenPos === null) {
                continue;
            }
            /** @var string $variableName */
            $variableName = $this->nodeNameResolver->getName($assignedVariableUse);
            $nodesByTypeAndPosition[] = new \Rector\DeadCode\ValueObject\VariableNodeUse($startTokenPos, $variableName, \Rector\DeadCode\ValueObject\VariableNodeUse::TYPE_USE, $assignedVariableUse);
        }
        return $this->sortByStart($nodesByTypeAndPosition);
    }
    /**
     * @param VariableNodeUse[] $nodesByTypeAndPosition
     * @return VariableNodeUse[]
     */
    private function sortByStart(array $nodesByTypeAndPosition) : array
    {
        \usort($nodesByTypeAndPosition, function (\Rector\DeadCode\ValueObject\VariableNodeUse $firstVariableNodeUse, \Rector\DeadCode\ValueObject\VariableNodeUse $secondVariableNodeUse) : int {
            return $firstVariableNodeUse->getStartTokenPosition() <=> $secondVariableNodeUse->getStartTokenPosition();
        });
        return $nodesByTypeAndPosition;
    }
}
