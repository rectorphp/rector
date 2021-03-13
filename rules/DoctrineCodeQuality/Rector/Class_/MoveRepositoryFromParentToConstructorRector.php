<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\DoctrineCodeQuality\NodeFactory\RepositoryAssignFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DoctrineCodeQuality\Rector\DoctrineRepositoryAsService\DoctrineRepositoryAsServiceTest
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

    public function __construct(
        ClassDependencyManipulator $classDependencyManipulator,
        ClassInsertManipulator $classInsertManipulator,
        RepositoryAssignFactory $repositoryAssignFactory
    ) {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->repositoryAssignFactory = $repositoryAssignFactory;
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
        if ($node->extends === null) {
            return null;
        }

        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Doctrine\ORM\EntityRepository') {
            return null;
        }

        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        if (! Strings::endsWith($className, 'Repository')) {
            return null;
        }

        // remove parent class
        $node->extends = null;

        // add $repository property
        $this->classInsertManipulator->addPropertyToClass(
            $node,
            'repository',
            new ObjectType('Doctrine\ORM\EntityRepository')
        );

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
