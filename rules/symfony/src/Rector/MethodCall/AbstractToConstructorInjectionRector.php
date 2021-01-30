<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    public function autowireAbstractToConstructorInjectionRector(
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
        $classLike = $methodCall->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $this->addConstructorDependencyToClass($classLike, $serviceType, $propertyName);

        return $this->nodeFactory->createPropertyFetch('this', $propertyName);
    }

    private function getServiceTypeFromMethodCallArgument(MethodCall $methodCall): ?Type
    {
        if (! isset($methodCall->args[0])) {
            return new MixedType();
        }

        $argument = $methodCall->args[0]->value;
        $serviceMap = $this->applicationServiceMapProvider->provide();

        if ($argument instanceof String_) {
            return $serviceMap->getServiceType($argument->value);
        }

        if ($argument instanceof ClassConstFetch && $argument->class instanceof Name) {
            $className = $this->getName($argument->class);

            return new ObjectType($className);
        }

        return new MixedType();
    }
}
