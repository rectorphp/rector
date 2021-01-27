<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Doctrine\NodeFactory\RepositoryNodeFactory;
use Rector\Doctrine\Type\RepositoryTypeFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://www.luzanky.cz/ for sponsoring this rule
 *
 * @see https://tomasvotruba.com/blog/2017/10/16/how-to-use-repository-with-doctrine-as-service-in-symfony/
 *
 * @see \Rector\Doctrine\Tests\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector\ServiceEntityRepositoryParentCallToDIRectorTest
 */
final class ServiceEntityRepositoryParentCallToDIRector extends AbstractRector
{
    /**
     * @var string
     */
    private const SERVICE_ENTITY_REPOSITORY_CLASS = 'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository';

    /**
     * @var RepositoryNodeFactory
     */
    private $repositoryNodeFactory;

    /**
     * @var RepositoryTypeFactory
     */
    private $repositoryTypeFactory;

    public function __construct(
        RepositoryNodeFactory $repositoryNodeFactory,
        RepositoryTypeFactory $repositoryTypeFactory
    ) {
        $this->repositoryNodeFactory = $repositoryNodeFactory;
        $this->repositoryTypeFactory = $repositoryTypeFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change ServiceEntityRepository to dependency injection, with repository property',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
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
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        // 1. remove params
        $node->params = [];

        // 2. remove parent::__construct()
        $entityReferenceExpr = $this->removeParentConstructAndCollectEntityReference($node);

        // 3. add $entityManager->getRepository() fetch assign
        $node->stmts[] = $this->repositoryNodeFactory->createRepositoryAssign($entityReferenceExpr);

        // 4. add $repository property
        $this->addRepositoryProperty($classLike, $entityReferenceExpr);

        // 5. add param + add property, dependency
        $this->addServiceConstructorDependencyToClass($classLike, 'Doctrine\ORM\EntityManagerInterface');

        return $node;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if (! $this->isName($classMethod, MethodName::CONSTRUCT)) {
            return true;
        }

        /** @var string|null $parentClassName */
        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return true;
        }

        return $parentClassName !== self::SERVICE_ENTITY_REPOSITORY_CLASS;
    }

    private function removeParentConstructAndCollectEntityReference(ClassMethod $classMethod): Expr
    {
        $entityReferenceExpr = null;

        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            &$entityReferenceExpr
        ) {
            if (! $node instanceof StaticCall) {
                return null;
            }

            if (! $this->isName($node->class, 'parent')) {
                return null;
            }

            $entityReferenceExpr = $node->args[1]->value;
            $this->removeNode($node);
        });

        if ($entityReferenceExpr === null) {
            throw new ShouldNotHappenException();
        }

        return $entityReferenceExpr;
    }

    private function addRepositoryProperty(Class_ $class, Expr $entityReferenceExpr): void
    {
        $genericObjectType = $this->repositoryTypeFactory->createRepositoryPropertyType($entityReferenceExpr);
        $this->addPropertyToClass($class, $genericObjectType, 'repository');
    }
}
