<?php declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

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
use Rector\Doctrine\ValueObject\DoctrineClass;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ManagerRegistryGetManagerToEntityManagerRector extends AbstractRector
{
    /**
     * @var string
     */
    private $managerRegistryClass;

    /**
     * @var string
     */
    private $objectManagerClass;

    public function __construct(
        string $managerRegistryClass = DoctrineClass::MANAGER_REGISTRY,
        string $objectManagerClass = DoctrineClass::OBJECT_MANAGER
    ) {
        $this->managerRegistryClass = $managerRegistryClass;
        $this->objectManagerClass = $objectManagerClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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

        $shouldRemoveManagerRegistryProperty = $registryCalledMethods === ['getManager'];
        if (! in_array('getManager', $registryCalledMethods, true)) {
            return null;
        }

        $registryParam = $this->resolveManagerRegistryParam($constructMethodNode);

        // no registry manager in the constructor
        if ($registryParam === null) {
            return null;
        }

        // add entity manager via constructor
        $this->addConstructorDependencyWithProperty(
            $node,
            $constructMethodNode,
            'entityManager',
            DoctrineClass::ENTITY_MANAGER
        );

        if ($shouldRemoveManagerRegistryProperty === true) {
            $this->removeManagerRegistryDependency($node, $constructMethodNode, $registryParam);
        }

        // remove assign method calls
        $this->removeAssignGetRepositoryCalls($node);

        $this->traverseNodesWithCallable($node->stmts, function (Node $node): ?PropertyFetch {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->isType($node, $this->objectManagerClass)) {
                return null;
            }

            return new PropertyFetch(new Variable('this'), 'entityManager');
        });

        return $node;
    }

    private function isRegistryGetManagerMethodCall(Assign $assign): bool
    {
        if (! $assign->expr instanceof MethodCall) {
            return false;
        }

        if (! $this->isType($assign->expr->var, $this->managerRegistryClass)) {
            return false;
        }

        if (! $this->isName($assign->expr->name, 'getManager')) {
            return false;
        }

        return true;
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

    private function createEntityManagerParam(): Param
    {
        return new Param(new Variable('entityManager'), null, new FullyQualified(DoctrineClass::ENTITY_MANAGER));
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

            if (! $this->isType($node->var, $this->managerRegistryClass)) {
                return null;
            }

            $name = $this->getName($node);
            if ($name === null) {
                return null;
            }

            $registryCalledMethods[] = $name;
        });

        return array_unique($registryCalledMethods);
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

            if (! $this->isName($param->type, $this->managerRegistryClass)) {
                continue;
            }

            unset($classMethod->params[$key]);
        }

        $this->removeRegistryDependencyAssign($class, $classMethod, $registryParam);
    }

    private function addConstructorDependencyWithProperty(
        Class_ $class,
        ClassMethod $classMethod,
        string $name,
        string $type
    ): void {
        $propertyFetch = new PropertyFetch(new Variable('this'), $name);
        $assign = new Assign($propertyFetch, new Variable($name));

        $classMethod->stmts[] = new Expression($assign);

        $this->addPropertyToClass($class, $type, $name);
    }

    private function resolveManagerRegistryParam(ClassMethod $classMethod): ?Param
    {
        foreach ($classMethod->params as $param) {
            if ($param->type === null) {
                continue;
            }

            if (! $this->isName($param->type, $this->managerRegistryClass)) {
                continue;
            }

            $classMethod->params[] = $this->createEntityManagerParam();

            return $param;
        }

        return null;
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
}
