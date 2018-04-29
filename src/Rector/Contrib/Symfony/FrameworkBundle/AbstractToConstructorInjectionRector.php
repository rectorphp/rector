<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\FrameworkBundle;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Bridge\Contract\ServiceTypeForNameProviderInterface;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Naming\PropertyNaming;
use Rector\Node\Attribute;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

abstract class AbstractToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var PropertyNaming
     */
    protected $propertyNaming;

    /**
     * @var ClassPropertyCollector
     */
    protected $classPropertyCollector;

    /**
     * @var PropertyFetchNodeFactory
     */
    protected $propertyFetchNodeFactory;

    /**
     * @var ServiceTypeForNameProviderInterface
     */
    protected $serviceTypeForNameProvider;

    /**
     * @var MethodCallAnalyzer
     */
    protected $methodCallAnalyzer;

    public function __construct(
        PropertyNaming $propertyNaming,
        ClassPropertyCollector $classPropertyCollector,
        PropertyFetchNodeFactory $propertyFetchNodeFactory,
        ServiceTypeForNameProviderInterface $serviceTypeForNameProvider,
        MethodCallAnalyzer $methodCallAnalyzer
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->classPropertyCollector = $classPropertyCollector;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
        $this->serviceTypeForNameProvider = $serviceTypeForNameProvider;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $serviceType = $this->getServiceTypeFromMethodCallArgument($methodCallNode);

        if ($serviceType === null) {
            return null;
        }

        $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);

        $this->classPropertyCollector->addPropertyForClass(
            (string) $methodCallNode->getAttribute(Attribute::CLASS_NAME),
            [$serviceType],
            $propertyName
        );

        return $this->propertyFetchNodeFactory->createLocalWithPropertyName($propertyName);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    private function getServiceTypeFromMethodCallArgument(Node $methodCallNode): ?string
    {
        if (! isset($methodCallNode->args[0])) {
            return null;
        }

        $argument = $methodCallNode->args[0]->value;

        if ($argument instanceof String_) {
            $serviceName = $argument->value;
            return $this->serviceTypeForNameProvider->provideTypeForName($serviceName);
        }

        if (! $argument instanceof ClassConstFetch) {
            return null;
        }

        if ($argument->class instanceof Name) {
            return $argument->class->getAttribute(Attribute::RESOLVED_NAME)->toString();
        }

        return null;
    }
}
