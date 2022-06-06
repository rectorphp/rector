<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\Rector\Class_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericObjectType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\NodeManipulator\ClassDependencyManipulator;
use RectorPrefix20220606\Rector\Core\NodeManipulator\ClassInsertManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver;
use RectorPrefix20220606\Rector\Doctrine\NodeFactory\RepositoryAssignFactory;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Set\DoctrineRepositoryAsServiceSet\DoctrineRepositoryAsServiceSetTest
 */
final class MoveRepositoryFromParentToConstructorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\RepositoryAssignFactory
     */
    private $repositoryAssignFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver
     */
    private $entityObjectTypeResolver;
    public function __construct(ClassDependencyManipulator $classDependencyManipulator, ClassInsertManipulator $classInsertManipulator, RepositoryAssignFactory $repositoryAssignFactory, EntityObjectTypeResolver $entityObjectTypeResolver)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->repositoryAssignFactory = $repositoryAssignFactory;
        $this->entityObjectTypeResolver = $entityObjectTypeResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns parent EntityRepository class to constructor dependency', [new CodeSample(<<<'CODE_SAMPLE'
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

final class PostRepository extends EntityRepository
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
        if (!$this->isObjectType($node, new ObjectType('Doctrine\\ORM\\EntityRepository'))) {
            return null;
        }
        if ($node->extends === null) {
            return null;
        }
        // remove parent class
        $node->extends = null;
        $entityObjectType = $this->entityObjectTypeResolver->resolveFromRepositoryClass($node);
        $genericObjectType = new GenericObjectType('Doctrine\\ORM\\EntityRepository', [$entityObjectType]);
        // add $repository property
        $this->classInsertManipulator->addPropertyToClass($node, 'repository', $genericObjectType);
        // add $entityManager and assign to constuctor
        $repositoryAssign = $this->repositoryAssignFactory->create($node);
        $this->classDependencyManipulator->addConstructorDependencyWithCustomAssign($node, 'entityManager', new ObjectType('Doctrine\\ORM\\EntityManagerInterface'), $repositoryAssign);
        return $node;
    }
}
