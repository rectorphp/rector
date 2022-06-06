<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\Rector\Foreach_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Stmt\Foreach_;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\ThisType;
use RectorPrefix20220606\Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer;
use RectorPrefix20220606\Rector\Core\Rector\AbstractScopeAwareRector;
use RectorPrefix20220606\Rector\NodeNestingScope\ValueObject\ControlStructure;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\ReadWrite\NodeFinder\NodeUsageFinder;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\ForeachItemsAssignToEmptyArrayToAssignRectorTest
 */
final class ForeachItemsAssignToEmptyArrayToAssignRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\ReadWrite\NodeFinder\NodeUsageFinder
     */
    private $nodeUsageFinder;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer
     */
    private $foreachAnalyzer;
    public function __construct(NodeUsageFinder $nodeUsageFinder, ForeachAnalyzer $foreachAnalyzer)
    {
        $this->nodeUsageFinder = $nodeUsageFinder;
        $this->foreachAnalyzer = $foreachAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change foreach() items assign to empty array to direct assign', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $collectedItems = [];

        foreach ($items as $item) {
             $collectedItems[] = $item;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $collectedItems = [];

        $collectedItems = $items;
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
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        /** @var Expr $assignVariable */
        $assignVariable = $this->foreachAnalyzer->matchAssignItemsOnlyForeachArrayVariable($node);
        return new Assign($assignVariable, $node->expr);
    }
    private function shouldSkip(Foreach_ $foreach, Scope $scope) : bool
    {
        $assignVariable = $this->foreachAnalyzer->matchAssignItemsOnlyForeachArrayVariable($foreach);
        if (!$assignVariable instanceof Expr) {
            return \true;
        }
        if ($this->shouldSkipAsPartOfOtherLoop($foreach)) {
            return \true;
        }
        $previousDeclaration = $this->nodeUsageFinder->findPreviousForeachNodeUsage($foreach, $assignVariable);
        if (!$previousDeclaration instanceof Node) {
            return \true;
        }
        $previousDeclarationParentNode = $previousDeclaration->getAttribute(AttributeKey::PARENT_NODE);
        if (!$previousDeclarationParentNode instanceof Assign) {
            return \true;
        }
        // must be empty array, otherwise it will false override
        $defaultValue = $this->valueResolver->getValue($previousDeclarationParentNode->expr);
        if ($defaultValue !== []) {
            return \true;
        }
        $type = $scope->getType($foreach->expr);
        if ($type instanceof ObjectType) {
            return $type->isIterable()->yes();
        }
        if ($type instanceof ThisType) {
            return $type->isIterable()->yes();
        }
        return \false;
    }
    private function shouldSkipAsPartOfOtherLoop(Foreach_ $foreach) : bool
    {
        $foreachParent = $this->betterNodeFinder->findParentByTypes($foreach, ControlStructure::LOOP_NODES);
        return $foreachParent instanceof Node;
    }
}
