<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeCollector\NodeByTypeAndPositionCollector;
use Rector\DeadCode\NodeFinder\VariableUseFinder;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\DeadCode\ValueObject\VariableNodeUse;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\RemoveOverriddenValuesRectorTest
 */
final class RemoveOverriddenValuesRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\NodeNestingScope\ContextAnalyzer $contextAnalyzer, \Rector\DeadCode\NodeCollector\NodeByTypeAndPositionCollector $nodeByTypeAndPositionCollector, \Rector\DeadCode\NodeFinder\VariableUseFinder $variableUseFinder, \Rector\Core\Php\ReservedKeywordAnalyzer $reservedKeywordAnalyzer, \Rector\DeadCode\SideEffect\SideEffectNodeDetector $sideEffectNodeDetector)
    {
        $this->contextAnalyzer = $contextAnalyzer;
        $this->nodeByTypeAndPositionCollector = $nodeByTypeAndPositionCollector;
        $this->variableUseFinder = $variableUseFinder;
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove initial assigns of overridden values', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\FunctionLike::class];
    }
    /**
     * @param FunctionLike $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
    private function resolveAssignedVariables(\PhpParser\Node\FunctionLike $functionLike) : array
    {
        return $this->betterNodeFinder->find($functionLike, function (\PhpParser\Node $node) : bool {
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            // skin in if
            if ($this->contextAnalyzer->isInIf($node)) {
                return \false;
            }
            // is variable on the left
            /** @var Assign $assignNode */
            $assignNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
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
                $comments = $variableNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS);
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
    private function isAssignNodeUsed(?\Rector\DeadCode\ValueObject\VariableNodeUse $previousNode, \Rector\DeadCode\ValueObject\VariableNodeUse $nodeByTypeAndPosition) : bool
    {
        // this node was just used, skip to next one
        if (!$previousNode instanceof \Rector\DeadCode\ValueObject\VariableNodeUse) {
            return \false;
        }
        if (!$previousNode->isType(\Rector\DeadCode\ValueObject\VariableNodeUse::TYPE_ASSIGN)) {
            return \false;
        }
        return $nodeByTypeAndPosition->isType(\Rector\DeadCode\ValueObject\VariableNodeUse::TYPE_USE);
    }
    private function shouldRemoveAssignNode(?\Rector\DeadCode\ValueObject\VariableNodeUse $previousNode, \Rector\DeadCode\ValueObject\VariableNodeUse $nodeByTypeAndPosition) : bool
    {
        if ($previousNode === null) {
            return \false;
        }
        if (!$previousNode->isType(\Rector\DeadCode\ValueObject\VariableNodeUse::TYPE_ASSIGN)) {
            return \false;
        }
        if (!$nodeByTypeAndPosition->isType(\Rector\DeadCode\ValueObject\VariableNodeUse::TYPE_ASSIGN)) {
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
        $isVariableAssigned = (bool) $this->betterNodeFinder->findFirst($assignNode->expr, function (\PhpParser\Node $node) use($nodeByTypeAndPosition) : bool {
            return $this->nodeComparator->areNodesEqual($node, $nodeByTypeAndPosition->getVariableNode());
        });
        return !$isVariableAssigned;
    }
}
