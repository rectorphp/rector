<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver;
use Rector\Doctrine\NodeFactory\RepositoryAssignFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
     * @var \Rector\Doctrine\NodeFactory\RepositoryAssignFactory
     */
    private $repositoryAssignFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\EntityObjectTypeResolver
     */
    private $entityObjectTypeResolver;
    public function __construct(ClassDependencyManipulator $classDependencyManipulator, RepositoryAssignFactory $repositoryAssignFactory, EntityObjectTypeResolver $entityObjectTypeResolver)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
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
        if (!$node->extends instanceof Name) {
            return null;
        }
        // remove parent class
        $node->extends = null;
        $subtractableType = $this->entityObjectTypeResolver->resolveFromRepositoryClass($node);
        $genericObjectType = new GenericObjectType('Doctrine\\ORM\\EntityRepository', [$subtractableType]);
        // add $repository property
        if (!$node->getProperty('repository') instanceof Property) {
            $repositoryProperty = $this->nodeFactory->createPrivatePropertyFromNameAndType('repository', $genericObjectType);
            $node->stmts = \array_merge([$repositoryProperty], $node->stmts);
        }
        // add $entityManager and assign to constuctor
        $repositoryAssign = $this->repositoryAssignFactory->create($node);
        $this->classDependencyManipulator->addConstructorDependencyWithCustomAssign($node, 'entityManager', new ObjectType('Doctrine\\ORM\\EntityManagerInterface'), $repositoryAssign);
        return $node;
    }
}
