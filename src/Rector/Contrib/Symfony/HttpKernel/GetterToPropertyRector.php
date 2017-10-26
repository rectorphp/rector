<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\HttpKernel;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Rector\Naming\PropertyNaming;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\Contrib\Symfony\ContainerCallAnalyzer;
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
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var ClassPropertyCollector
     */
    private $classPropertyCollector;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var ContainerCallAnalyzer
     */
    private $containerCallAnalyzer;

    /**
     * @var ServiceTypeForNameProviderInterface
     */
    private $serviceTypeForNameProvider;

    public function __construct(
        PropertyNaming $propertyNaming,
        ClassPropertyCollector $classPropertyCollector,
        NodeFactory $nodeFactory,
        ContainerCallAnalyzer $containerCallAnalyzer,
        ServiceTypeForNameProviderInterface $serviceTypeForNameProvider
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->nodeFactory = $nodeFactory;
        $this->containerCallAnalyzer = $containerCallAnalyzer;
        $this->serviceTypeForNameProvider = $serviceTypeForNameProvider;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        return $this->containerCallAnalyzer->isThisCall($node);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $serviceName = $methodCallNode->args[0]->value->value;
        $serviceType = $this->serviceTypeForNameProvider->provideTypeForName($serviceName);
        if ($serviceType === null) {
            return null;
        }

        $propertyName = $this->propertyNaming->typeToName($serviceType);

        $this->classPropertyCollector->addPropertyForClass(
            (string) $methodCallNode->getAttribute(Attribute::CLASS_NAME),
            [$serviceType],
            $propertyName
        );

        return $this->nodeFactory->createLocalPropertyFetch($propertyName);
    }
}
