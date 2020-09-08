<?php

declare(strict_types=1);

namespace Rector\Architecture\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\Bridge\RectorProviderException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Doctrine\Contract\Mapper\DoctrineEntityAndRepositoryMapperInterface;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\DoctrineRepositoryAsService\DoctrineRepositoryAsServiceTest
 */
final class ServiceLocatorToDIRector extends AbstractRector
{
    /**
     * @var DoctrineEntityAndRepositoryMapperInterface
     */
    private $doctrineEntityAndRepositoryMapper;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(
        DoctrineEntityAndRepositoryMapperInterface $doctrineEntityAndRepositoryMapper,
        PropertyNaming $propertyNaming
    ) {
        $this->doctrineEntityAndRepositoryMapper = $doctrineEntityAndRepositoryMapper;
        $this->propertyNaming = $propertyNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns $this->getRepository() in Symfony Controller to constructor injection and private property access.',
            [
                new CodeSample(
                    <<<'PHP'
class ProductController extends Controller
{
    public function someAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getRepository('SomethingBundle:Product')->findSomething(...);
    }
}
PHP
                    ,
                    <<<'PHP'
class ProductController extends Controller
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function someAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $this->productRepository->findSomething(...);
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'getRepository')) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        // possible mocking → skip
        if ($firstArgumentValue instanceof StaticCall) {
            return null;
        }

        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;
        if (count($methodCallNode->args) !== 1) {
            return null;
        }

        if ($methodCallNode->args[0]->value instanceof String_) {
            /** @var String_ $string */
            $string = $methodCallNode->args[0]->value;

            // is alias
            if (Strings::contains($string->value, ':')) {
                return null;
            }
        }

        if (Strings::endsWith($className, 'Repository')) {
            return null;
        }

        $repositoryFqn = $this->resolveRepositoryFqnFromGetRepositoryMethodCall($node);
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        $repositoryObjectType = new ObjectType($repositoryFqn);

        $this->addConstructorDependencyToClass(
            $classLike,
            $repositoryObjectType,
            $this->propertyNaming->fqnToVariableName($repositoryObjectType)
        );

        return $this->createPropertyFetch('this', $this->propertyNaming->fqnToVariableName($repositoryObjectType));
    }

    private function resolveRepositoryFqnFromGetRepositoryMethodCall(MethodCall $methodCall): string
    {
        $entityFqnOrAlias = $this->entityFqnOrAlias($methodCall);

        if ($entityFqnOrAlias !== null) {
            $repositoryClassName = $this->doctrineEntityAndRepositoryMapper->mapEntityToRepository($entityFqnOrAlias);
            if ($repositoryClassName !== null) {
                return $repositoryClassName;
            }
        }

        throw new RectorProviderException(sprintf(
            'A repository was not provided for "%s" entity by your "%s" class.',
            $entityFqnOrAlias,
            get_class($this->doctrineEntityAndRepositoryMapper)
        ));
    }

    private function entityFqnOrAlias(MethodCall $methodCall): string
    {
        $repositoryArgument = $methodCall->args[0]->value;

        if ($repositoryArgument instanceof String_) {
            return $repositoryArgument->value;
        }

        if ($repositoryArgument instanceof ClassConstFetch && $repositoryArgument->class instanceof Name) {
            return $this->getName($repositoryArgument->class);
        }

        throw new ShouldNotHappenException('Unable to resolve repository argument');
    }
}
