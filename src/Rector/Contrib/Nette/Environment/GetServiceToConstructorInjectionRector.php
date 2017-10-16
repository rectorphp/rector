<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Environment;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Rector\Naming\PropertyNaming;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 * Environment::get('some_service') # where "some_service" is name of the service in container.
 *
 * into:
 * $this->someService # where "someService" is type of the service
 */
final class GetServiceToConstructorInjectionRector extends AbstractRector
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
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var ServiceTypeForNameProviderInterface
     */
    private $serviceTypeForNameProvider;

    public function __construct(
        PropertyNaming $propertyNaming,
        ClassPropertyCollector $classPropertyCollector,
        NodeFactory $nodeFactory,
        MethodCallAnalyzer $methodCallAnalyzer,
        ServiceTypeForNameProviderInterface $serviceTypeForNameProvider
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->nodeFactory = $nodeFactory;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->serviceTypeForNameProvider = $serviceTypeForNameProvider;
    }

    public function isCandidate(Node $node): bool
    {
        return $this->methodCallAnalyzer->isStaticMethodCallTypeAndMethod($node, 'Nette\Environment', 'getService');
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
            $serviceType,
            $propertyName
        );

        return $this->nodeFactory->createLocalPropertyFetch($propertyName);
    }
}
