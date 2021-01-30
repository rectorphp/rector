<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\Contract\Mapper\DoctrineEntityAndRepositoryMapperInterface;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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

    /**
     * @var \Rector\Doctrine\NodeAnalyzer\EntityClassOrAliasResolver
     */
    private $entityClassOrAliasResolver;

    public function __construct(
        DoctrineEntityAndRepositoryMapperInterface $doctrineEntityAndRepositoryMapper,
        PropertyNaming $propertyNaming,
        \Rector\Doctrine\NodeAnalyzer\EntityClassOrAliasResolver $entityClassOrAliasResolver
    ) {
        $this->doctrineEntityAndRepositoryMapper = $doctrineEntityAndRepositoryMapper;
        $this->propertyNaming = $propertyNaming;
        $this->entityClassOrAliasResolver = $entityClassOrAliasResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns $this->getRepository() in Symfony Controller to constructor injection and private property access.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class ProductController extends Controller
{
    public function someAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getRepository('SomethingBundle:Product')->findSomething(...);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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

        $repositoryFqn = $this->entityClassOrAliasResolver->resolve($node);
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
}
