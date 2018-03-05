<?php declare(strict_types=1);

namespace Rector\Rector\Architecture\RepositoryAsService;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\Builder\Class_\VariableInfo;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Builder\PropertyBuilder;
use Rector\Contract\Bridge\EntityForDoctrineRepositoryProviderInterface;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;

final class MoveRepositoryFromParentToConstructorRector extends AbstractRector
{
    /**
     * @var PropertyBuilder
     */
    private $propertyBuilder;

    /**
     * @var ConstructorMethodBuilder
     */
    private $constructorMethodBuilder;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var EntityForDoctrineRepositoryProviderInterface
     */
    private $entityForDoctrineRepositoryProvider;

    public function __construct(
        PropertyBuilder $propertyBuilder,
        ConstructorMethodBuilder $constructorMethodBuilder,
        NodeFactory $nodeFactory,
        EntityForDoctrineRepositoryProviderInterface $entityForDoctrineRepositoryProvider
    ) {
        $this->propertyBuilder = $propertyBuilder;
        $this->constructorMethodBuilder = $constructorMethodBuilder;
        $this->nodeFactory = $nodeFactory;
        $this->entityForDoctrineRepositoryProvider = $entityForDoctrineRepositoryProvider;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        if (! $node->extends) {
            return false;
        }

        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if ($parentClassName !== 'Doctrine\ORM\EntityRepository') {
            return false;
        }

        $className = $node->getAttribute(Attribute::CLASS_NAME);

        return Strings::endsWith($className, 'Repository');
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // remove parent class
        $node->extends = null;

        // add $repository property
        $propertyInfo = VariableInfo::createFromNameAndTypes('repository', ['Doctrine\ORM\EntityRepository']);
        $this->propertyBuilder->addPropertyToClass($node, $propertyInfo);

        // add $entityManager and assign to constuctor
        $this->constructorMethodBuilder->addParameterAndAssignToConstructorArgumentsOfClass(
            $node,
            VariableInfo::createFromNameAndTypes('entityManager', ['Doctrine\ORM\EntityManager']),
            $this->createRepositoryAssign($node)
        );

        return $node;
    }

    /**
     * Creates:
     * "$this->repository = $entityManager->getRepository()"
     */
    private function createRepositoryAssign(Class_ $classNode): Expression
    {
        $repositoryClassName = (string) $classNode->getAttribute(Attribute::CLASS_NAME);
        $entityClassName = $this->entityForDoctrineRepositoryProvider->provideEntityForRepository($repositoryClassName);

        $entityClassConstantReferenceNode = $this->nodeFactory->createClassConstantReference($entityClassName);

        $getRepositoryMethodCallNode = new MethodCall(
            new Variable('entityManager'),
            'getRepository',
            [$entityClassConstantReferenceNode]
        );

        return $this->nodeFactory->createPropertyAssignmentWithExpr('repository', $getRepositoryMethodCallNode);
    }
}
