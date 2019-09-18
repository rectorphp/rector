<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Bridge\Contract\DoctrineEntityAndRepositoryMapperInterface;
use Rector\Exception\Bridge\RectorProviderException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class MoveRepositoryFromParentToConstructorRector extends AbstractRector
{
    /**
     * @var string
     */
    private $entityManagerClass;

    /**
     * @var string
     */
    private $entityRepositoryClass;

    /**
     * @var DoctrineEntityAndRepositoryMapperInterface
     */
    private $doctrineEntityAndRepositoryMapper;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(
        DoctrineEntityAndRepositoryMapperInterface $doctrineEntityAndRepositoryMapper,
        ClassManipulator $classManipulator,
        string $entityRepositoryClass = 'Doctrine\ORM\EntityRepository',
        string $entityManagerClass = 'Doctrine\ORM\EntityManager'
    ) {
        $this->doctrineEntityAndRepositoryMapper = $doctrineEntityAndRepositoryMapper;
        $this->entityRepositoryClass = $entityRepositoryClass;
        $this->entityManagerClass = $entityManagerClass;
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns parent EntityRepository class to constructor dependency', [
            new ConfiguredCodeSample(
                <<<'PHP'
namespace App\Repository;

use Doctrine\ORM\EntityRepository;

final class PostRepository extends EntityRepository
{
}
PHP
                ,
                <<<'PHP'
namespace App\Repository;

use App\Entity\Post;
use Doctrine\ORM\EntityRepository;

final class PostRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;
    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(\App\Entity\Post::class);
    }
}
PHP
                ,
                [
                    '$entityRepositoryClass' => 'Doctrine\ORM\EntityRepository',
                    '$entityManagerClass' => 'Doctrine\ORM\EntityManager',
                ]
            ),
        ]);
    }

    /**
     * @return string[]
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
        if ($parentClassName !== $this->entityRepositoryClass) {
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
        $this->classManipulator->addPropertyToClass($node, 'repository', new ObjectType($this->entityRepositoryClass));

        // add $entityManager and assign to constuctor
        $this->classManipulator->addConstructorDependencyWithCustomAssign(
            $node,
            'entityManager',
            new ObjectType($this->entityManagerClass),
            $this->createRepositoryAssign($node)
        );

        return $node;
    }

    /**
     * Creates:
     * "$this->repository = $entityManager->getRepository()"
     */
    private function createRepositoryAssign(Class_ $classNode): Assign
    {
        $repositoryClassName = (string) $classNode->getAttribute(AttributeKey::CLASS_NAME);
        $entityClassName = $this->doctrineEntityAndRepositoryMapper->mapRepositoryToEntity($repositoryClassName);

        if ($entityClassName === null) {
            throw new RectorProviderException(sprintf(
                'An entity was not provided for "%s" repository by your "%s" class.',
                $repositoryClassName,
                get_class($this->doctrineEntityAndRepositoryMapper)
            ));
        }

        $entityClassConstantReferenceNode = $this->createClassConstantReference($entityClassName);

        $getRepositoryMethodCallNode = $this->builderFactory->methodCall(
            new Variable('entityManager'),
            'getRepository',
            [$entityClassConstantReferenceNode]
        );

        return $this->createPropertyAssignmentWithExpr('repository', $getRepositoryMethodCallNode);
    }
}
