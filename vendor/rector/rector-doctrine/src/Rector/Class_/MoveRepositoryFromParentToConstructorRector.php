<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver;
use Rector\Doctrine\NodeFactory\RepositoryAssignFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Set\DoctrineRepositoryAsServiceSet\DoctrineRepositoryAsServiceSetTest
 */
final class MoveRepositoryFromParentToConstructorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    /**
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @var \Rector\Doctrine\NodeFactory\RepositoryAssignFactory
     */
    private $repositoryAssignFactory;
    /**
     * @var \Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver
     */
    private $entityObjectTypeResolver;
    public function __construct(\Rector\Core\NodeManipulator\ClassDependencyManipulator $classDependencyManipulator, \Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\Doctrine\NodeFactory\RepositoryAssignFactory $repositoryAssignFactory, \Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver $entityObjectTypeResolver)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->repositoryAssignFactory = $repositoryAssignFactory;
        $this->entityObjectTypeResolver = $entityObjectTypeResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns parent EntityRepository class to constructor dependency', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('Doctrine\\ORM\\EntityRepository'))) {
            return null;
        }
        if ($node->extends === null) {
            return null;
        }
        // remove parent class
        $node->extends = null;
        $entityObjectType = $this->entityObjectTypeResolver->resolveFromRepositoryClass($node);
        $genericObjectType = new \PHPStan\Type\Generic\GenericObjectType('Doctrine\\ORM\\EntityRepository', [$entityObjectType]);
        // add $repository property
        $this->classInsertManipulator->addPropertyToClass($node, 'repository', $genericObjectType);
        // add $entityManager and assign to constuctor
        $repositoryAssign = $this->repositoryAssignFactory->create($node);
        $this->classDependencyManipulator->addConstructorDependencyWithCustomAssign($node, 'entityManager', new \PHPStan\Type\ObjectType('Doctrine\\ORM\\EntityManagerInterface'), $repositoryAssign);
        return $node;
    }
}
