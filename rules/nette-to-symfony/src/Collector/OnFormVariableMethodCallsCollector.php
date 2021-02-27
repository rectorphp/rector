<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Collector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class OnFormVariableMethodCallsCollector
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeComparator
     */
    private $nodeComparator;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeTypeResolver $nodeTypeResolver,
        NodeComparator $nodeComparator
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeComparator = $nodeComparator;
    }

    /**
     * @return MethodCall[]
     */
    public function collectFromClassMethod(ClassMethod $classMethod): array
    {
        $newFormVariable = $this->resolveNewFormVariable($classMethod);
        if (! $newFormVariable instanceof Expr) {
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

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->getStmts(),
            function (Node $node) use (&$newFormVariable): ?int {
                if (! $node instanceof Assign) {
                    return null;
                }

                if (! $this->nodeTypeResolver->isObjectType(
                    $node->expr,
                    new ObjectType('Nette\Application\UI\Form')
                )) {
                    return null;
                }

                $newFormVariable = $node->var;

                return NodeTraverser::STOP_TRAVERSAL;
            }
        );

        return $newFormVariable;
    }

    /**
     * @return MethodCall[]
     */
    private function collectOnFormVariableMethodCalls(ClassMethod $classMethod, Expr $expr): array
    {
        $onFormVariableMethodCalls = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->getStmts(),
            function (Node $node) use ($expr, &$onFormVariableMethodCalls) {
                if (! $node instanceof MethodCall) {
                    return null;
                }

                if (! $this->nodeComparator->areNodesEqual($node->var, $expr)) {
                    return null;
                }

                $onFormVariableMethodCalls[] = $node;

                return null;
            }
        );

        return $onFormVariableMethodCalls;
    }
}
