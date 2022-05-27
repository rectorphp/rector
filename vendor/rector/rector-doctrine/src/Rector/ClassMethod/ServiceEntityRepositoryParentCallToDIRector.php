<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Doctrine\NodeFactory\RepositoryNodeFactory;
use Rector\Doctrine\Type\RepositoryTypeFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://tomasvotruba.com/blog/2017/10/16/how-to-use-repository-with-doctrine-as-service-in-symfony/
 * @see https://getrector.org/blog/2021/02/08/how-to-instantly-decouple-symfony-doctrine-repository-inheritance-to-clean-composition
 *
 * @see \Rector\Doctrine\Tests\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector\ServiceEntityRepositoryParentCallToDIRectorTest
 */
final class ServiceEntityRepositoryParentCallToDIRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\RepositoryNodeFactory
     */
    private $repositoryNodeFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\Type\RepositoryTypeFactory
     */
    private $repositoryTypeFactory;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(RepositoryNodeFactory $repositoryNodeFactory, RepositoryTypeFactory $repositoryTypeFactory, PropertyToAddCollector $propertyToAddCollector, ClassDependencyManipulator $classDependencyManipulator, PropertyNaming $propertyNaming, ReflectionResolver $reflectionResolver)
    {
        $this->repositoryNodeFactory = $repositoryNodeFactory;
        $this->repositoryTypeFactory = $repositoryTypeFactory;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->propertyNaming = $propertyNaming;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change ServiceEntityRepository to dependency injection, with repository property', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProjectRepository extends ServiceEntityRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository<Project>
     */
    private $repository;

    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Project::class);
        $this->entityManager = $entityManager;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     *
     * For reference, possible manager registry param types:
     * - Doctrine\Common\Persistence\ManagerRegistry
     * - Doctrine\Persistence\ManagerRegistry
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        // 1. remove parent::__construct()
        $entityReferenceExpr = $this->removeParentConstructAndCollectEntityReference($node);
        if (!$entityReferenceExpr instanceof Expr) {
            return null;
        }
        // 2. remove params
        $node->params = [];
        // 3. add $entityManager->getRepository() fetch assign
        $repositoryAssign = $this->repositoryNodeFactory->createRepositoryAssign($entityReferenceExpr);
        $entityManagerObjectType = new ObjectType('Doctrine\\ORM\\EntityManagerInterface');
        $this->classDependencyManipulator->addConstructorDependencyWithCustomAssign($class, 'entityManager', $entityManagerObjectType, $repositoryAssign);
        $this->addRepositoryProperty($class, $entityReferenceExpr);
        // 5. add param + add property, dependency
        $propertyName = $this->propertyNaming->fqnToVariableName($entityManagerObjectType);
        $propertyMetadata = new PropertyMetadata($propertyName, $entityManagerObjectType, Class_::MODIFIER_PRIVATE);
        $this->propertyToAddCollector->addPropertyToClass($class, $propertyMetadata);
        return $node;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        if (!$this->isName($classMethod, MethodName::CONSTRUCT)) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            // fresh node or possibly trait/interface
            return \true;
        }
        return !$classReflection->isSubclassOf('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository');
    }
    private function removeParentConstructAndCollectEntityReference(ClassMethod $classMethod) : ?Expr
    {
        $entityReferenceExpr = null;
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$entityReferenceExpr) {
            if (!$node instanceof StaticCall) {
                return null;
            }
            if (!$this->isName($node->class, 'parent')) {
                return null;
            }
            $entityReferenceExpr = $node->args[1]->value;
            $this->removeNode($node);
        });
        return $entityReferenceExpr;
    }
    private function addRepositoryProperty(Class_ $class, Expr $entityReferenceExpr) : void
    {
        $genericObjectType = $this->repositoryTypeFactory->createRepositoryPropertyType($entityReferenceExpr);
        $this->propertyToAddCollector->addPropertyWithoutConstructorToClass('repository', $genericObjectType, $class);
    }
}
