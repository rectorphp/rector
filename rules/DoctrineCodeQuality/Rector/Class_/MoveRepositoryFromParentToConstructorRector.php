<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\DoctrineCodeQuality\NodeAnalyzer\EntityObjectTypeResolver;
use Rector\DoctrineCodeQuality\NodeFactory\RepositoryAssignFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\DoctrineRepositoryAsServiceSetTest
 */
final class MoveRepositoryFromParentToConstructorRector extends AbstractRector
{
    /**
     * @var ClassDependencyManipulator
     */
    private $classDependencyManipulator;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    /**
     * @var RepositoryAssignFactory
     */
    private $repositoryAssignFactory;

    /**
     * @var EntityObjectTypeResolver
     */
    private $entityObjectTypeResolver;

    public function __construct(
        ClassDependencyManipulator $classDependencyManipulator,
        ClassInsertManipulator $classInsertManipulator,
        RepositoryAssignFactory $repositoryAssignFactory,
        EntityObjectTypeResolver $entityObjectTypeResolver
    ) {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->repositoryAssignFactory = $repositoryAssignFactory;
        $this->entityObjectTypeResolver = $entityObjectTypeResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns parent EntityRepository class to constructor dependency',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

final class PostRepository extends EntityRepository
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

final class PostRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository<Post>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Post::class);
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
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
        if (! $this->isObjectType($node, new ObjectType('Doctrine\ORM\EntityRepository'))) {
            return null;
        }

        // remove parent class
        $node->extends = null;

        $entityObjectType = $this->entityObjectTypeResolver->resolveFromRepositoryClass($node);
        $genericObjectType = new GenericObjectType('Doctrine\ORM\EntityRepository', [$entityObjectType]);

        // add $repository property
        $this->classInsertManipulator->addPropertyToClass($node, 'repository', $genericObjectType);

        // add $entityManager and assign to constuctor
        $repositoryAssign = $this->repositoryAssignFactory->create($node);

        $this->classDependencyManipulator->addConstructorDependencyWithCustomAssign(
            $node,
            'entityManager',
            new ObjectType('Doctrine\ORM\EntityManagerInterface'),
            $repositoryAssign
        );

        return $node;
    }
}
