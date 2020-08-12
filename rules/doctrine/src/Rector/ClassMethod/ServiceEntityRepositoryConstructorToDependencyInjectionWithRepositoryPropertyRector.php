<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

/**
 * @sponsor Thanks https://www.luzanky.cz/ for sponsoring this rule
 *
 * @see https://tomasvotruba.com/blog/2017/10/16/how-to-use-repository-with-doctrine-as-service-in-symfony/
 *
 * @see \Rector\Doctrine\Tests\Rector\ClassMethod\ServiceEntityRepositoryConstructorToDependencyInjectionWithRepositoryPropertyRector\ServiceEntityRepositoryConstructorToDependencyInjectionWithRepositoryPropertyRectorTest
 */
final class ServiceEntityRepositoryConstructorToDependencyInjectionWithRepositoryPropertyRector extends AbstractRector
{
    /**
     * @var string
     */
    private const SERVICE_ENTITY_REPOSITORY_CLASS = 'Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository';

    /**
     * @var Expr|null
     */
    private $entityReference;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change ServiceEntityRepository to dependency injection, with repository property',
            [
                new CodeSample(
                    <<<'PHP'
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }
}
PHP
                    ,
                    <<<'PHP'
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProjectRepository extends ServiceEntityRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    private $repository;

    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Project::class);
        $this->entityManager = $entityManager;
    }
}
PHP
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
     * - Doctrine\Common\Persistence\ManagerRegistry
     * - Doctrine\Persistence\ManagerRegistry
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var ClassLike|null $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        // 1. remove params
        $node->params = [];

        // 2. remove parent::__construct()
        $this->removeParentConstructAndCollectEntityReference($node);

        // 3. add $entityManager->getRepository() fetch assign
        $node->stmts[] = $this->createRepositoryAssign();

        // 4. add $repository property
        $this->addRepositoryProperty($class);

        // 5. add param + add property, dependency
        $this->addEntityManagerDependency($class);

        return $node;
    }

    /**
     * @todo extract to RepositoryNodeFactory
     */
    private function createRepositoryAssign(): Expression
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), new Identifier('repository'));
        $assign = new Assign($propertyFetch, $this->createGetRepositoryMethodCall());

        return new Expression($assign);
    }

    /**
     * @todo extract to RepositoryNodeFactory
     */
    private function createGetRepositoryMethodCall(): MethodCall
    {
        if ($this->entityReference === null) {
            throw new ShouldNotHappenException();
        }

        return new MethodCall(new Variable('entityManager'), 'getRepository', [new Arg($this->entityReference)]);
    }

    /**
     * @todo extract to RepositoryNodeFactory
     */
    private function addEntityManagerDependency(Class_ $class): void
    {
        $entityManagerType = new ObjectType('Doctrine\ORM\EntityManagerInterface');
        $this->addConstrutorDependencyToClass($class, $entityManagerType, 'entityManager');
    }

    private function removeParentConstructAndCollectEntityReference(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof StaticCall) {
                return null;
            }

            if (! $this->isName($node->class, 'parent')) {
                return null;
            }

            $this->entityReference = $node->args[1]->value;
            $this->removeNode($node);
        });
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        /** @var string|null $parentClassName */
        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return true;
        }

        return $parentClassName !== self::SERVICE_ENTITY_REPOSITORY_CLASS;
    }

    /**
     * @todo extract to RepositoryNodeFactory
     */
    private function addRepositoryProperty(Class_ $class): void
    {
        if ($this->entityReference === null) {
            throw new ShouldNotHappenException();
        }

        if ($this->entityReference instanceof ClassConstFetch) {
            /** @var string $className */
            $className = $this->getName($this->entityReference->class);
        } else {
            throw new NotImplementedYetException();
        }

        $this->addPropertyToClass(
            $class,
            new GenericObjectType('Doctrine\ORM\EntityRepository', [new FullyQualifiedObjectType($className)]),
            'repository'
        );
    }
}
