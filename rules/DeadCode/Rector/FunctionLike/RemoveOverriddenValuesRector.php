<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\Rector\FunctionLike;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\Rector\Core\Php\ReservedKeywordAnalyzer;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\DeadCode\NodeCollector\NodeByTypeAndPositionCollector;
use RectorPrefix20220606\Rector\DeadCode\NodeFinder\VariableUseFinder;
use RectorPrefix20220606\Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use RectorPrefix20220606\Rector\DeadCode\ValueObject\VariableNodeUse;
use RectorPrefix20220606\Rector\NodeNestingScope\ContextAnalyzer;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\RemoveOverriddenValuesRectorTest
 */
final class RemoveOverriddenValuesRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeCollector\NodeByTypeAndPositionCollector
     */
    private $nodeByTypeAndPositionCollector;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeFinder\VariableUseFinder
     */
    private $variableUseFinder;
    /**
     * @readonly
     * @var \Rector\Core\Php\ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;
    public function __construct(ContextAnalyzer $contextAnalyzer, NodeByTypeAndPositionCollector $nodeByTypeAndPositionCollector, VariableUseFinder $variableUseFinder, ReservedKeywordAnalyzer $reservedKeywordAnalyzer, SideEffectNodeDetector $sideEffectNodeDetector)
    {
        $this->contextAnalyzer = $contextAnalyzer;
        $this->nodeByTypeAndPositionCollector = $nodeByTypeAndPositionCollector;
        $this->variableUseFinder = $variableUseFinder;
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove initial assigns of overridden values', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
         $possibleDirectories = [];
         $directories = array_filter($possibleDirectories, 'file_exists');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FunctionLike::class];
    }
    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node) : ?Node
    {
        // 1. collect assigns
        $assignedVariables = $this->resolveAssignedVariables($node);
        $assignedVariableNames = $this->getNodeNames($assignedVariables);
        // 2. collect use of those variables
        $assignedVariablesUse = $this->variableUseFinder->resolveUsedVariables($node, $assignedVariables);
        $nodesByTypeAndPosition = $this->nodeByTypeAndPositionCollector->collectNodesByTypeAndPosition($assignedVariables, $assignedVariablesUse, $node);
        $nodesToRemove = $this->resolveNodesToRemove($assignedVariableNames, $nodesByTypeAndPosition);
        if ($nodesToRemove === []) {
            return null;
        }
        $this->nodeRemover->removeNodes($nodesToRemove);
        return $node;
    }
    /**
     * @return Variable[]
     */
    private function resolveAssignedVariables(FunctionLike $functionLike) : array
    {
        return $this->betterNodeFinder->find($functionLike, function (Node $node) use($functionLike) : bool {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof Assign) {
                return \false;
            }
            if (!$node instanceof Variable) {
                return \false;
            }
            // skin in if
            if ($this->contextAnalyzer->isInIf($node)) {
                return \false;
            }
            // is variable on the left
            /** @var Assign $assignNode */
            $assignNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($assignNode->var !== $node) {
                return \false;
            }
            if ($this->sideEffectNodeDetector->detectCallExpr($assignNode->expr)) {
                return \false;
            }
            // simple variable only
            if (!\is_string($node->name)) {
                return \false;
            }
            $parentFunctionLike = $this->betterNodeFinder->findParentType($node, FunctionLike::class);
            if ($parentFunctionLike !== $functionLike) {
                return \false;
            }
            return !$this->reservedKeywordAnalyzer->isNativeVariable($node->name);
        });
    }
    /**
     * @param Node[] $nodes
     * @return string[]
     */
    private function getNodeNames(array $nodes) : array
    {
        $nodeNames = [];
        foreach ($nodes as $node) {
            $nodeName = $this->getName($node);
            if (\is_string($nodeName)) {
                $nodeNames[] = $nodeName;
            }
        }
        return \array_unique($nodeNames);
    }
    /**
     * @param string[] $assignedVariableNames
     * @param VariableNodeUse[] $nodesByTypeAndPosition
     * @return Node[]
     */
    private function resolveNodesToRemove(array $assignedVariableNames, array $nodesByTypeAndPosition) : array
    {
        $nodesToRemove = [];
        foreach ($assignedVariableNames as $assignedVariableName) {
            $previousNode = null;
            foreach ($nodesByTypeAndPosition as $nodes) {
                $variableNode = $nodes->getVariableNode();
                $comments = $variableNode->getAttribute(AttributeKey::COMMENTS);
                if ($comments !== null) {
                    continue;
                }
                $nodesIsName = $nodes->isName($assignedVariableName);
                if (!$nodesIsName) {
                    continue;
                }
                if ($this->isAssignNodeUsed($previousNode, $nodes)) {
                    // continue
                    // instant override → remove
                } elseif ($this->shouldRemoveAssignNode($previousNode, $nodes)) {
                    /** @var VariableNodeUse $previousNode */
                    $nodesToRemove[] = $previousNode->getParentNode();
                }
                $previousNode = $nodes;
            }
        }
        return $nodesToRemove;
    }
    private function isAssignNodeUsed(?VariableNodeUse $previousNode, VariableNodeUse $nodeByTypeAndPosition) : bool
    {
        // this node was just used, skip to next one
        if (!$previousNode instanceof VariableNodeUse) {
            return \false;
        }
        if (!$previousNode->isType(VariableNodeUse::TYPE_ASSIGN)) {
            return \false;
        }
        return $nodeByTypeAndPosition->isType(VariableNodeUse::TYPE_USE);
    }
    private function shouldRemoveAssignNode(?VariableNodeUse $previousNode, VariableNodeUse $nodeByTypeAndPosition) : bool
    {
        if ($previousNode === null) {
            return \false;
        }
        if (!$previousNode->isType(VariableNodeUse::TYPE_ASSIGN)) {
            return \false;
        }
        if (!$nodeByTypeAndPosition->isType(VariableNodeUse::TYPE_ASSIGN)) {
            return \false;
        }
        // check the nesting level, e.g. call in if/while/else etc.
        if ($previousNode->getNestingHash() !== $nodeByTypeAndPosition->getNestingHash()) {
            return \false;
        }
        // check previous node doesn't contain the node on the right, e.g.
        // $someNode = 1;
        // $someNode = $someNode ?: 1;
        /** @var Assign $assignNode */
        $assignNode = $nodeByTypeAndPosition->getParentNode();
        $isVariableAssigned = (bool) $this->betterNodeFinder->findFirst($assignNode->expr, function (Node $node) use($nodeByTypeAndPosition) : bool {
            return $this->nodeComparator->areNodesEqual($node, $nodeByTypeAndPosition->getVariableNode());
        });
        return !$isVariableAssigned;
    }
}
