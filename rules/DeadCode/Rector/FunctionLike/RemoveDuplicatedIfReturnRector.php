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
use PHPStan\Type\BooleanType;
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
final class RemoveDuplicatedIfReturnRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\DeadCode\NodeCollector\ModifiedVariableNamesCollector $modifiedVariableNamesCollector, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->modifiedVariableNamesCollector = $modifiedVariableNamesCollector;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->nodeComparator = $nodeComparator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove duplicated if stmt with return in function/method body', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\FunctionLike::class];
    }
    /**
     * @param FunctionLike $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
        if (!\in_array(\get_class($cond), [\PhpParser\Node\Expr\Variable::class, \PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\StaticPropertyFetch::class], \true)) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getType($cond);
        if (!$type instanceof \PHPStan\Type\BooleanType) {
            return \false;
        }
        $next = $ifWithOnlyReturns[0]->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$next instanceof \PhpParser\Node\Stmt\Return_) {
            return \false;
        }
        $expr = $next->expr;
        if (!$expr instanceof \PhpParser\Node\Expr) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($expr, $cond)) {
            return \false;
        }
        /** @var Return_ $returnStmt */
        $returnStmt = $ifWithOnlyReturns[0]->stmts[0];
        if (!$returnStmt->expr instanceof \PhpParser\Node\Expr) {
            return \false;
        }
        return $this->valueResolver->isValue($returnStmt->expr, \true);
    }
    /**
     * @return If_[][]
     */
    private function collectDuplicatedIfWithOnlyReturnByHash(\PhpParser\Node\FunctionLike $functionLike) : array
    {
        $ifWithOnlyReturnsByHash = [];
        $modifiedVariableNames = [];
        foreach ((array) $functionLike->getStmts() as $stmt) {
            if (!$this->ifManipulator->isIfWithOnly($stmt, \PhpParser\Node\Stmt\Return_::class)) {
                // variable modification
                $modifiedVariableNames = \array_merge($modifiedVariableNames, $this->modifiedVariableNamesCollector->collectModifiedVariableNames($stmt));
                continue;
            }
            if ($this->containsVariableNames($stmt, $modifiedVariableNames)) {
                continue;
            }
            /** @var If_ $stmt */
            $isFoundPropertyFetch = (bool) $this->betterNodeFinder->findFirst($stmt->cond, function (\PhpParser\Node $node) : bool {
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
    private function containsVariableNames(\PhpParser\Node\Stmt $stmt, array $modifiedVariableNames) : bool
    {
        if ($modifiedVariableNames === []) {
            return \false;
        }
        $containsVariableNames = \false;
        $this->traverseNodesWithCallable($stmt, function (\PhpParser\Node $node) use($modifiedVariableNames, &$containsVariableNames) : ?int {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if (!$this->isNames($node, $modifiedVariableNames)) {
                return null;
            }
            $containsVariableNames = \true;
            return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
        });
        return $containsVariableNames;
    }
}
