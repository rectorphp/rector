<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

/**
 * @see \Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\ManagerRegistryGetManagerToEntityManagerRectorTest
 */
final class ManagerRegistryGetManagerToEntityManagerRector extends AbstractRector
{
    /**
     * @var string
     */
    private const GET_MANAGER = 'getManager';

    /**
     * @var string
     */
    private const ENTITY_MANAGER = 'entityManager';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('', [
            new CodeSample(
                <<<'PHP'
use Doctrine\Common\Persistence\ManagerRegistry;

class CustomRepository
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function run()
    {
        $entityManager = $this->managerRegistry->getManager();
        $someRepository = $entityManager->getRepository('Some');
    }
}
PHP
                ,
                <<<'PHP'
use Doctrine\ORM\EntityManagerInterface;

class CustomRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function run()
    {
        $someRepository = $this->entityManager->getRepository('Some');
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $constructMethodNode = $node->getMethod('__construct');
        if ($constructMethodNode === null) {
            return null;
        }

        // collect on registry method calls, so we know if the manager registry is needed
        $registryCalledMethods = $this->resolveManagerRegistryCalledMethodNames($node);
        if (! in_array(self::GET_MANAGER, $registryCalledMethods, true)) {
            return null;
        }

        $managerRegistryParam = $this->resolveManagerRegistryParam($constructMethodNode);

        // no registry manager in the constructor
        if ($managerRegistryParam === null) {
            return null;
        }

        if ($registryCalledMethods === [self::GET_MANAGER]) {
            // the manager registry is needed only get entity manager → we don't need it now
            $this->removeManagerRegistryDependency($node, $constructMethodNode, $managerRegistryParam);
        }

        $this->replaceEntityRegistryVariableWithEntityManagerProperty($node);
        $this->removeAssignGetRepositoryCalls($node);

        // add entity manager via constructor
        $this->addConstructorDependencyWithProperty(
            $node,
            $constructMethodNode,
            self::ENTITY_MANAGER,
            new FullyQualifiedObjectType(EntityManagerInterface::class)
        );

        return $node;
    }

    /**
     * @return string[]
     */
    private function resolveManagerRegistryCalledMethodNames(Class_ $class): array
    {
        $registryCalledMethods = [];
        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (&$registryCalledMethods) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isObjectType($node->var, ManagerRegistry::class)) {
                return null;
            }

            $name = $this->getName($node->name);
            if ($name === null) {
                return null;
            }

            $registryCalledMethods[] = $name;
        });

        return array_unique($registryCalledMethods);
    }

    private function resolveManagerRegistryParam(ClassMethod $classMethod): ?Param
    {
        foreach ($classMethod->params as $param) {
            if ($param->type === null) {
                continue;
            }

            if (! $this->isName($param->type, ManagerRegistry::class)) {
                continue;
            }

            $classMethod->params[] = $this->createEntityManagerParam();

            return $param;
        }

        return null;
    }

    private function removeManagerRegistryDependency(
        Class_ $class,
        ClassMethod $classMethod,
        Param $registryParam
    ): void {
        // remove constructor param: $managerRegistry
        foreach ($classMethod->params as $key => $param) {
            if ($param->type === null) {
                continue;
            }

            if (! $this->isName($param->type, ManagerRegistry::class)) {
                continue;
            }

            unset($classMethod->params[$key]);
        }

        $this->removeRegistryDependencyAssign($class, $classMethod, $registryParam);
    }

    /**
     * Before:
     * $entityRegistry->
     *
     * After:
     * $this->entityManager->
     */
    private function replaceEntityRegistryVariableWithEntityManagerProperty(Class_ $node): void
    {
        $this->traverseNodesWithCallable($node->stmts, function (Node $node): ?PropertyFetch {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->isObjectType($node, ObjectManager::class)) {
                return null;
            }

            return new PropertyFetch(new Variable('this'), self::ENTITY_MANAGER);
        });
    }

    private function removeAssignGetRepositoryCalls(Class_ $class): void
    {
        $this->traverseNodesWithCallable($class->stmts, function (Node $node) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->isRegistryGetManagerMethodCall($node)) {
                return null;
            }

            $this->removeNode($node);
        });
    }

    private function addConstructorDependencyWithProperty(
        Class_ $class,
        ClassMethod $classMethod,
        string $name,
        ObjectType $objectType
    ): void {
        $assign = $this->createSameNameThisAssign($name);
        $classMethod->stmts[] = new Expression($assign);

        $this->addPropertyToClass($class, $objectType, $name);
    }

    private function createEntityManagerParam(): Param
    {
        return new Param(new Variable(self::ENTITY_MANAGER), null, new FullyQualified(EntityManagerInterface::class));
    }

    private function removeRegistryDependencyAssign(Class_ $class, ClassMethod $classMethod, Param $registryParam): void
    {
        foreach ((array) $classMethod->stmts as $constructorMethodStmt) {
            if (! $constructorMethodStmt instanceof Expression && ! $constructorMethodStmt->expr instanceof Assign) {
                continue;
            }

            /** @var Assign $assign */
            $assign = $constructorMethodStmt->expr;
            if (! $this->areNamesEqual($assign->expr, $registryParam->var)) {
                continue;
            }

            $this->removeManagerRegistryProperty($class, $assign);

            // remove assign
            $this->removeNodeFromStatements($classMethod, $constructorMethodStmt);

            break;
        }
    }

    private function isRegistryGetManagerMethodCall(Assign $assign): bool
    {
        if (! $assign->expr instanceof MethodCall) {
            return false;
        }

        if (! $this->isObjectType($assign->expr->var, ManagerRegistry::class)) {
            return false;
        }
        return $this->isName($assign->expr->name, self::GET_MANAGER);
    }

    /**
     * Creates: "$this->value = $value;"
     */
    private function createSameNameThisAssign(string $name): Assign
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), $name);

        return new Assign($propertyFetch, new Variable($name));
    }

    private function removeManagerRegistryProperty(Class_ $class, Assign $assign): void
    {
        $managerRegistryPropertyName = $this->getName($assign->var);

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            $managerRegistryPropertyName
        ): ?int {
            if (! $node instanceof Property) {
                return null;
            }

            if (! $this->isName($node, $managerRegistryPropertyName)) {
                return null;
            }

            $this->removeNode($node);

            return NodeTraverser::STOP_TRAVERSAL;
        });
    }
}
