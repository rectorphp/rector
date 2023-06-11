<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Doctrine\NodeFactory\RepositoryNodeFactory;
use Rector\Doctrine\Type\RepositoryTypeFactory;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    public function __construct(RepositoryNodeFactory $repositoryNodeFactory, RepositoryTypeFactory $repositoryTypeFactory, ClassDependencyManipulator $classDependencyManipulator, PropertyNaming $propertyNaming)
    {
        $this->repositoryNodeFactory = $repositoryNodeFactory;
        $this->repositoryTypeFactory = $repositoryTypeFactory;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->propertyNaming = $propertyNaming;
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
    private \Doctrine\ORM\EntityManagerInterface $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository<Project>
     */
    private \Doctrine\ORM\EntityRepository $repository;

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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     *
     * For reference, possible manager registry param types:
     *
     * - Doctrine\Common\Persistence\ManagerRegistry
     * - Doctrine\Persistence\ManagerRegistry
     */
    public function refactor(Node $node) : ?Node
    {
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        $classScope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$classScope instanceof Scope) {
            return null;
        }
        $classReflection = $classScope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->isSubclassOf('Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository')) {
            return null;
        }
        // 1. remove parent::__construct()
        $entityReferenceExpr = $this->removeParentConstructAndCollectEntityReference($constructClassMethod);
        if (!$entityReferenceExpr instanceof Expr) {
            return null;
        }
        // 2. remove params
        $constructClassMethod->params = [];
        // 3. add $entityManager->getRepository() fetch assign
        $repositoryAssign = $this->repositoryNodeFactory->createRepositoryAssign($entityReferenceExpr);
        $entityManagerObjectType = new ObjectType('Doctrine\\ORM\\EntityManagerInterface');
        $this->classDependencyManipulator->addConstructorDependencyWithCustomAssign($node, 'entityManager', $entityManagerObjectType, $repositoryAssign);
        $this->addRepositoryProperty($node, $entityReferenceExpr);
        // 5. add param + add property, dependency
        $propertyName = $this->propertyNaming->fqnToVariableName($entityManagerObjectType);
        // add property as first element
        $propertyMetadata = new PropertyMetadata($propertyName, $entityManagerObjectType);
        $this->classDependencyManipulator->addConstructorDependency($node, $propertyMetadata);
        return $node;
    }
    private function removeParentConstructAndCollectEntityReference(ClassMethod $classMethod) : ?Expr
    {
        if ($classMethod->stmts === null) {
            return null;
        }
        foreach ($classMethod->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof StaticCall) {
                continue;
            }
            $staticCall = $stmt->expr;
            if (!$this->isName($staticCall->class, 'parent')) {
                continue;
            }
            if ($staticCall->isFirstClassCallable()) {
                continue;
            }
            unset($classMethod->stmts[$key]);
            $args = $staticCall->getArgs();
            return $args[1]->value;
        }
        return null;
    }
    private function addRepositoryProperty(Class_ $class, Expr $entityReferenceExpr) : void
    {
        if ($class->getProperty('repository') instanceof Property) {
            return;
        }
        $genericObjectType = $this->repositoryTypeFactory->createRepositoryPropertyType($entityReferenceExpr);
        $property = $this->nodeFactory->createPrivatePropertyFromNameAndType('repository', $genericObjectType);
        $class->stmts = \array_merge([$property], $class->stmts);
    }
}
