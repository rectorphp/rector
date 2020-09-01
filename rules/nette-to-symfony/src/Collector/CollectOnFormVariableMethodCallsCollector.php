<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Collector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class CollectOnFormVariableMethodCallsCollector
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        CallableNodeTraverser $callableNodeTraverser,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @return MethodCall[]
     */
    public function collectFromClassMethod(ClassMethod $classMethod): array
    {
        $newFormVariable = $this->resolveNewFormVariable($classMethod);
        if ($newFormVariable === null) {
            return [];
        }

        return $this->collectOnFormVariableMethodCalls($classMethod, $newFormVariable);
    }

    /**
     * Matches:
     * $form = new Form;
     */
    private function resolveNewFormVariable(ClassMethod $classMethod): ?Expr
    {
        $newFormVariable = null;

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use (
            &$newFormVariable
        ): ?int {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->nodeTypeResolver->isObjectType($node->expr, 'Nette\Application\UI\Form')) {
                return null;
            }

            $newFormVariable = $node->var;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $newFormVariable;
    }

    /**
     * @return MethodCall[]
     */
    private function collectOnFormVariableMethodCalls(ClassMethod $classMethod, Expr $expr): array
    {
        $onFormVariableMethodCalls = [];
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use (
            $expr,
            &$onFormVariableMethodCalls
        ) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($node->var, $expr)) {
                return null;
            }

            $onFormVariableMethodCalls[] = $node;

            return null;
        });

        return $onFormVariableMethodCalls;
    }
}
