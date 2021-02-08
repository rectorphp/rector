<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class StmtsManipulator
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @param Stmt[] $stmts
     */
    public function getUnwrappedLastStmt(array $stmts): ?Node
    {
        $lastStmtKey = array_key_last($stmts);
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
    public function filterOutExistingStmts(ClassMethod $classMethod, array $stmts): array
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node) use (&$stmts) {
                foreach ($stmts as $key => $assign) {
                    if (! $this->betterStandardPrinter->areNodesEqual($node, $assign)) {
                        continue;
                    }

                    unset($stmts[$key]);
                }

                return null;
            }
        );

        return $stmts;
    }
}
