<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\CakePHPToSymfony\Contract\NodeManipulator\RepositoryFindMethodCallManipulatorInterface;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;

final class DoctrineRepositoryClassMethodManipulator
{
    /**
     * @var RepositoryFindMethodCallManipulatorInterface[]
     */
    private $repositoryFindMethodCallManipulators = [];

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @param RepositoryFindMethodCallManipulatorInterface[] $repositoryFindMethodCallManipulators
     */
    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        NodeNameResolver $nodeNameResolver,
        ValueResolver $valueResolver,
        array $repositoryFindMethodCallManipulators
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->repositoryFindMethodCallManipulators = $repositoryFindMethodCallManipulators;
    }

    public function createFromCakePHPClassMethod(ClassMethod $classMethod, string $entityClass): ClassMethod
    {
        $this->callableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->getStmts(),
            function (Node $node) use ($entityClass) {
                if (! $node instanceof MethodCall) {
                    return null;
                }

                if (! $this->nodeNameResolver->isName($node->name, 'find')) {
                    return null;
                }

                return $this->refactorClassMethodByKind($node, $entityClass);
            }
        );

        return $classMethod;
    }

    private function refactorClassMethodByKind(MethodCall $methodCall, string $entityClass): Node
    {
        $findKind = $this->valueResolver->getValue($methodCall->args[0]->value);

        foreach ($this->repositoryFindMethodCallManipulators as $repositoryFindMethodCallManipulator) {
            if ($findKind !== $repositoryFindMethodCallManipulator->getKeyName()) {
                continue;
            }

            return $repositoryFindMethodCallManipulator->processMethodCall($methodCall, $entityClass);
        }

        throw new NotImplementedException($findKind);
    }
}
