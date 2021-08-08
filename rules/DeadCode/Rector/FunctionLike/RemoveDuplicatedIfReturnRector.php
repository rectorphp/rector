<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
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
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @var \Rector\DeadCode\NodeCollector\ModifiedVariableNamesCollector
     */
    private $modifiedVariableNamesCollector;
    /**
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
        foreach ($ifWithOnlyReturnsByHash as $ifWithOnlyReturns) {
            // keep first one
            \array_shift($ifWithOnlyReturns);
            foreach ($ifWithOnlyReturns as $ifWithOnlyReturn) {
                $this->removeNode($ifWithOnlyReturn);
            }
        }
        return $node;
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
        return $this->filterOutSingleItemStmts($ifWithOnlyReturnsByHash);
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
    /**
     * @param array<string, If_[]> $ifWithOnlyReturnsByHash
     * @return array<string, If_[]>
     */
    private function filterOutSingleItemStmts(array $ifWithOnlyReturnsByHash) : array
    {
        return \array_filter($ifWithOnlyReturnsByHash, function (array $stmts) : bool {
            return \count($stmts) >= 2;
        });
    }
}
