<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class StmtsManipulator
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function getUnwrappedLastStmt(array $stmts) : ?Node
    {
        \end($stmts);
        $lastStmtKey = \key($stmts);
        $lastStmt = $stmts[$lastStmtKey];
        if ($lastStmt instanceof Expression) {
            return $lastStmt->expr;
        }
        return $lastStmt;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function filterOutExistingStmts(ClassMethod $classMethod, array $stmts) : array
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$stmts) {
            foreach ($stmts as $key => $assign) {
                if (!$this->nodeComparator->areNodesEqual($node, $assign)) {
                    continue;
                }
                unset($stmts[$key]);
            }
            return null;
        });
        return $stmts;
    }
    public function isVariableUsedInNextStmt(StmtsAwareInterface $stmtsAware, int $jumpToKey, string $variableName) : bool
    {
        if ($stmtsAware->stmts === null) {
            return \false;
        }
        $stmts = \array_slice($stmtsAware->stmts, $jumpToKey, null, \true);
        return (bool) $this->betterNodeFinder->findVariableOfName($stmts, $variableName);
    }
}
