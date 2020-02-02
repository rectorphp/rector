<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\CakePHPToSymfony\Contract\NodeManipulator\RepositoryFindMethodCallManipulatorInterface;
use Rector\Exception\NotImplementedException;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

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
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @param RepositoryFindMethodCallManipulatorInterface[] $repositoryFindMethodCallManipulators
     */
    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        NameResolver $nameResolver,
        ValueResolver $valueResolver,
        array $repositoryFindMethodCallManipulators
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
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

                if (! $this->nameResolver->isName($node->name, 'find')) {
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
