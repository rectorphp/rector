<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Doctrine\NodeAnalyzer\MethodCallNameOnTypeResolver;
use Rector\Doctrine\NodeManipulator\DependencyRemover;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\MethodCallNameOnTypeResolver
     */
    private $methodCallNameOnTypeResolver;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\DependencyRemover
     */
    private $dependencyRemover;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(MethodCallNameOnTypeResolver $methodCallNameOnTypeResolver, DependencyRemover $dependencyRemover, PropertyToAddCollector $propertyToAddCollector, PhpVersionProvider $phpVersionProvider)
    {
        $this->methodCallNameOnTypeResolver = $methodCallNameOnTypeResolver;
        $this->dependencyRemover = $dependencyRemover;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes ManagerRegistry intermediate calls directly to EntityManager calls', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $constructorClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return null;
        }
        // collect on registry method calls, so we know if the manager registry is needed
        $registryCalledMethods = $this->methodCallNameOnTypeResolver->resolve($node, new ObjectType('Doctrine\\Common\\Persistence\\ManagerRegistry'));
        if (!\in_array(self::GET_MANAGER, $registryCalledMethods, \true)) {
            return null;
        }
        $managerRegistryParam = $this->resolveManagerRegistryParam($constructorClassMethod);
        // no registry manager in the constructor
        if (!$managerRegistryParam instanceof Param) {
            return null;
        }
        if ($registryCalledMethods === [self::GET_MANAGER]) {
            // the manager registry is needed only get entity manager → we don't need it now
            $this->removeManagerRegistryDependency($node, $constructorClassMethod, $managerRegistryParam);
        }
        $this->replaceEntityRegistryVariableWithEntityManagerProperty($node);
        $this->removeAssignGetRepositoryCalls($node);
        // add entity manager via constructor
        $this->addConstructorDependencyWithProperty($node, $constructorClassMethod, self::ENTITY_MANAGER, new FullyQualifiedObjectType('Doctrine\\ORM\\EntityManagerInterface'));
        return $node;
    }
    private function resolveManagerRegistryParam(ClassMethod $classMethod) : ?Param
    {
        foreach ($classMethod->params as $param) {
            if ($param->type === null) {
                continue;
            }
            if (!$this->isName($param->type, 'Doctrine\\Common\\Persistence\\ManagerRegistry')) {
                continue;
            }
            return $param;
        }
        return null;
    }
    private function removeManagerRegistryDependency(Class_ $class, ClassMethod $classMethod, Param $registryParam) : void
    {
        // remove constructor param: $managerRegistry
        foreach ($classMethod->params as $key => $param) {
            if ($param->type === null) {
                continue;
            }
            if (!$this->isName($param->type, 'Doctrine\\Common\\Persistence\\ManagerRegistry')) {
                continue;
            }
            unset($classMethod->params[$key]);
        }
        $this->dependencyRemover->removeByType($class, $classMethod, $registryParam, 'Doctrine\\Common\\Persistence\\ManagerRegistry');
    }
    /**
     * Before: $entityRegistry->
     *
     * After: $this->entityManager->
     */
    private function replaceEntityRegistryVariableWithEntityManagerProperty(Class_ $class) : void
    {
        $this->traverseNodesWithCallable($class->stmts, function (Node $node) : ?PropertyFetch {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->isObjectType($node, new ObjectType('Doctrine\\Common\\Persistence\\ObjectManager'))) {
                return null;
            }
            return new PropertyFetch(new Variable('this'), self::ENTITY_MANAGER);
        });
    }
    private function removeAssignGetRepositoryCalls(Class_ $class) : void
    {
        $this->traverseNodesWithCallable($class->stmts, function (Node $node) {
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$this->isRegistryGetManagerMethodCall($node)) {
                return null;
            }
            $this->removeNode($node);
        });
    }
    private function addConstructorDependencyWithProperty(Class_ $class, ClassMethod $classMethod, string $name, FullyQualifiedObjectType $fullyQualifiedObjectType) : void
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            $assign = $this->nodeFactory->createPropertyAssignment($name);
            $classMethod->stmts[] = new Expression($assign);
        }
        $propertyMetadata = new PropertyMetadata($name, $fullyQualifiedObjectType, Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
    }
    private function isRegistryGetManagerMethodCall(Assign $assign) : bool
    {
        if (!$assign->expr instanceof MethodCall) {
            return \false;
        }
        if (!$this->isObjectType($assign->expr->var, new ObjectType('Doctrine\\Common\\Persistence\\ManagerRegistry'))) {
            return \false;
        }
        return $this->isName($assign->expr->name, self::GET_MANAGER);
    }
}
