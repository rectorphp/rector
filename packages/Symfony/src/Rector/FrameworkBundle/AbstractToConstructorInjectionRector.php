<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\FrameworkBundle;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;

abstract class AbstractToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var PropertyNaming
     */
    protected $propertyNaming;

    /**
     * @var AnalyzedApplicationContainerInterface
     */
    protected $analyzedApplicationContainer;

    /**
     * @required
     */
    public function setAbstractToConstructorInjectionRectorDependencies(
        PropertyNaming $propertyNaming,
        AnalyzedApplicationContainerInterface $analyzedApplicationContainer
    ): void {
        $this->propertyNaming = $propertyNaming;
        $this->analyzedApplicationContainer = $analyzedApplicationContainer;
    }

    protected function processMethodCallNode(MethodCall $methodCallNode): ?Node
    {
        $serviceType = $this->getServiceTypeFromMethodCallArgument($methodCallNode);
        if ($serviceType === null) {
            return null;
        }

        $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);

        $this->addPropertyToClass(
            $methodCallNode->getAttribute(Attribute::CLASS_NODE),
            $serviceType,
            $propertyName
        );

        return $this->createPropertyFetch('this', $propertyName);
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
            return $this->analyzedApplicationContainer->getTypeForName($serviceName);
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
