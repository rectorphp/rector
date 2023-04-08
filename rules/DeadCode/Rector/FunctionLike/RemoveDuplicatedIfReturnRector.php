<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeCollector\ModifiedVariableNamesCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/rectorphp/rector/issues/2945
 *
 * @see \Rector\Tests\DeadCode\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\RemoveDuplicatedIfReturnRectorTest
 */
final class RemoveDuplicatedIfReturnRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeCollector\ModifiedVariableNamesCollector
     */
    private $modifiedVariableNamesCollector;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(IfManipulator $ifManipulator, ModifiedVariableNamesCollector $modifiedVariableNamesCollector, PropertyFetchAnalyzer $propertyFetchAnalyzer, NodeComparator $nodeComparator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->modifiedVariableNamesCollector = $modifiedVariableNamesCollector;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->nodeComparator = $nodeComparator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove duplicated if stmt with return in function/method body', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return true;
        }

        $value2 = 100;

        if ($value) {
            return true;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return true;
        }

        $value2 = 100;
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
        $ifWithOnlyReturnsByHash = $this->collectDuplicatedIfWithOnlyReturnByHash($node);
        if ($ifWithOnlyReturnsByHash === []) {
            return null;
        }
        $hasRemovedNode = \false;
        foreach ($ifWithOnlyReturnsByHash as $ifWithOnlyReturns) {
            $isBool = $this->isBoolVarIfCondReturnTrueNextReturnBoolVar($ifWithOnlyReturns);
            if (!$isBool && \count($ifWithOnlyReturns) < 2) {
                continue;
            }
            if (!$isBool) {
                // keep first one
                \array_shift($ifWithOnlyReturns);
            }
            foreach ($ifWithOnlyReturns as $ifWithOnlyReturn) {
                $this->removeNode($ifWithOnlyReturn);
                $hasRemovedNode = \true;
            }
        }
        if ($hasRemovedNode) {
            return $node;
        }
        return null;
    }
    /**
     * @param If_[] $ifWithOnlyReturns
     */
    private function isBoolVarIfCondReturnTrueNextReturnBoolVar(array $ifWithOnlyReturns) : bool
    {
        if (\count($ifWithOnlyReturns) > 1) {
            return \false;
        }
        /** @var Expr $cond */
        $cond = $ifWithOnlyReturns[0]->cond;
        if (!\in_array(\get_class($cond), [Variable::class, PropertyFetch::class, StaticPropertyFetch::class], \true)) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getType($cond);
        if (!$type->isBoolean()->yes()) {
            return \false;
        }
        $nextNode = $ifWithOnlyReturns[0]->getAttribute(AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof Return_) {
            return \false;
        }
        $expr = $nextNode->expr;
        if (!$expr instanceof Expr) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($expr, $cond)) {
            return \false;
        }
        /** @var Return_ $returnStmt */
        $returnStmt = $ifWithOnlyReturns[0]->stmts[0];
        if (!$returnStmt->expr instanceof Expr) {
            return \false;
        }
        return $this->valueResolver->isValue($returnStmt->expr, \true);
    }
    /**
     * @return If_[][]
     */
    private function collectDuplicatedIfWithOnlyReturnByHash(FunctionLike $functionLike) : array
    {
        $ifWithOnlyReturnsByHash = [];
        $modifiedVariableNames = [];
        foreach ((array) $functionLike->getStmts() as $stmt) {
            if (!$this->ifManipulator->isIfWithOnly($stmt, Return_::class)) {
                // variable modification
                $modifiedVariableNames = \array_merge($modifiedVariableNames, $this->modifiedVariableNamesCollector->collectModifiedVariableNames($stmt));
                continue;
            }
            if ($this->containsVariableNames($stmt, $modifiedVariableNames)) {
                continue;
            }
            /** @var If_ $stmt */
            $isFoundPropertyFetch = (bool) $this->betterNodeFinder->findFirst($stmt->cond, function (Node $node) : bool {
                return $this->propertyFetchAnalyzer->isPropertyFetch($node);
            });
            if ($isFoundPropertyFetch) {
                continue;
            }
            $hash = $this->nodeComparator->printWithoutComments($stmt);
            $ifWithOnlyReturnsByHash[$hash][] = $stmt;
        }
        return $ifWithOnlyReturnsByHash;
    }
    /**
     * @param string[] $modifiedVariableNames
     */
    private function containsVariableNames(Stmt $stmt, array $modifiedVariableNames) : bool
    {
        if ($modifiedVariableNames === []) {
            return \false;
        }
        $containsVariableNames = \false;
        $this->traverseNodesWithCallable($stmt, function (Node $node) use($modifiedVariableNames, &$containsVariableNames) : ?int {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->isNames($node, $modifiedVariableNames)) {
                return null;
            }
            $containsVariableNames = \true;
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $containsVariableNames;
    }
}
