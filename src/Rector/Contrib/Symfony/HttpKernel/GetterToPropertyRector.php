<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\HttpKernel;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\Naming\NameResolver;
use Rector\Contract\Bridge\ServiceNameToTypeProviderInterface;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\SymfonyContainerCallsAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 * $this->get('some_service') # where "some_service" is name of the service in container.
 *
 * into:
 * $this->someService # where "someService" is type of the service
 */
final class GetterToPropertyRector extends AbstractRector
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var SymfonyContainerCallsAnalyzer
     */
    private $symfonyContainerCallsAnalyzer;

    /**
     * @var ServiceNameToTypeProviderInterface
     */
    private $serviceNameToTypeProvider;

    public function __construct(
        NameResolver $nameResolver,
        ClassPropertyCollector $classPropertyCollector,
        NodeFactory $nodeFactory,
        SymfonyContainerCallsAnalyzer $symfonyContainerCallsAnalyzer,
        ServiceNameToTypeProviderInterface $serviceNameToTypeProvider
    ) {
        $this->nameResolver = $nameResolver;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->nodeFactory = $nodeFactory;
        $this->symfonyContainerCallsAnalyzer = $symfonyContainerCallsAnalyzer;
        $this->serviceNameToTypeProvider = $serviceNameToTypeProvider;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        return $this->symfonyContainerCallsAnalyzer->isThisCall($node);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $serviceName = $methodCallNode->args[0]->value->value;

        $serviceNameToTypeMap = $this->serviceNameToTypeProvider->provide();

        if (! isset($serviceNameToTypeMap[$serviceName])) {
            return null;
        }

        $serviceType = $serviceNameToTypeMap[$serviceName];

        $propertyName = $this->nameResolver->resolvePropertyNameFromType($serviceType);

        $this->classPropertyCollector->addPropertyForClass(
            (string) $methodCallNode->getAttribute(Attribute::CLASS_NAME),
            $serviceType,
            $propertyName
        );

        return $this->nodeFactory->createLocalPropertyFetch($propertyName);
    }
}
