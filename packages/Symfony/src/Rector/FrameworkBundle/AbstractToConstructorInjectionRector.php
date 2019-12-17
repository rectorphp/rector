<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\FrameworkBundle;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Exception\ShouldNotHappenException;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Symfony\ServiceMapProvider;

abstract class AbstractToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var PropertyNaming
     */
    protected $propertyNaming;

    /**
     * @var ServiceMapProvider
     */
    private $applicationServiceMapProvider;

    /**
     * @required
     */
    public function autowireAbstractToConstructorInjectionRectorDependencies(
        PropertyNaming $propertyNaming,
        ServiceMapProvider $applicationServiceMapProvider
    ): void {
        $this->propertyNaming = $propertyNaming;
        $this->applicationServiceMapProvider = $applicationServiceMapProvider;
    }

    protected function processMethodCallNode(MethodCall $methodCall): ?Node
    {
        $serviceType = $this->getServiceTypeFromMethodCallArgument($methodCall);
        if (! $serviceType instanceof ObjectType) {
            return null;
        }

        $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);
        $classNode = $methodCall->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $this->addPropertyToClass($classNode, $serviceType, $propertyName);

        return $this->createPropertyFetch('this', $propertyName);
    }

    /**
     * @param MethodCall $methodCallNode
     */
    private function getServiceTypeFromMethodCallArgument(Node $methodCallNode): Type
    {
        if (! isset($methodCallNode->args[0])) {
            return new MixedType();
        }

        $argument = $methodCallNode->args[0]->value;

        $serviceMap = $this->applicationServiceMapProvider->provide();

        if ($argument instanceof String_) {
            return $serviceMap->getServiceType($argument->value);
        }

        if (! $argument instanceof ClassConstFetch) {
            return new MixedType();
        }

        if ($argument->class instanceof Name) {
            $className = $this->getName($argument->class);

            return new ObjectType($className);
        }

        return new MixedType();
    }
}
