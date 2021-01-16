<?php

declare(strict_types=1);

namespace Rector\Naming;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ArrayDimFetchRenamer
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
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @see \Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector::renameVariableInClassMethod
     */
    public function renameToVariable(
        ClassMethod $classMethod,
        ArrayDimFetch $arrayDimFetch,
        string $variableName
    ): void {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (
            Node $node
        ) use ($arrayDimFetch, $variableName) {
            // do not rename element above
            if ($node->getLine() <= $arrayDimFetch->getLine()) {
                return null;
            }

            if ($this->isScopeNesting($node)) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($node, $arrayDimFetch)) {
                return null;
            }

            return new Variable($variableName);
        });
    }

    private function isScopeNesting(Node $node): bool
    {
        return $node instanceof Closure || $node instanceof Function_ || $node instanceof ArrowFunction;
    }
}
