<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeCollector\NodeFinder\MethodCallParsedNodesFinder;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionMethod;

final class ClassMethodExternalCallNodeAnalyzer
{
    /**
     * @var MethodCallParsedNodesFinder
     */
    private $methodCallParsedNodesFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var EventSubscriberMethodNamesResolver
     */
    private $eventSubscriberMethodNamesResolver;

    public function __construct(
        EventSubscriberMethodNamesResolver $eventSubscriberMethodNamesResolver,
        MethodCallParsedNodesFinder $methodCallParsedNodesFinder,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->methodCallParsedNodesFinder = $methodCallParsedNodesFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->eventSubscriberMethodNamesResolver = $eventSubscriberMethodNamesResolver;
    }

    public function hasExternalCall(ClassMethod $classMethod): bool
    {
        $methodCalls = $this->methodCallParsedNodesFinder->findByClassMethod($classMethod);

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if ($this->isArrayCallable($classMethod, $methodCalls, $methodName)) {
            return true;
        }

        if ($this->isEventSubscriberMethod($classMethod, $methodName)) {
            return true;
        }

        // remove static calls and [$this, 'call']
        /** @var MethodCall[] $methodCalls */
        $methodCalls = array_filter($methodCalls, function (object $node): bool {
            return $node instanceof MethodCall;
        });

        foreach ($methodCalls as $methodCall) {
            $callerType = $this->nodeTypeResolver->resolve($methodCall->var);
            if (! $callerType instanceof TypeWithClassName) {
                // unable to handle reliably
                return true;
            }

            // external call
            $nodeClassName = $methodCall->getAttribute(AttributeKey::CLASS_NAME);
            if ($nodeClassName !== $callerType->getClassName()) {
                return true;
            }

            /** @var string $methodName */
            $methodName = $this->nodeNameResolver->getName($classMethod);
            $reflectionMethod = new ReflectionMethod($nodeClassName, $methodName);
            // parent class name, must be at least protected
            if ($reflectionMethod->getDeclaringClass()->getName() !== $nodeClassName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param StaticCall[]|MethodCall[]|ArrayCallable[] $methodCalls
     */
    private function isArrayCallable(ClassMethod $classMethod, array $methodCalls, string $methodName): bool
    {
        /** @var ArrayCallable[] $arrayCallables */
        $arrayCallables = array_filter($methodCalls, function (object $node): bool {
            return $node instanceof ArrayCallable;
        });

        foreach ($arrayCallables as $arrayCallable) {
            $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
            if ($className === $arrayCallable->getClass() && $methodName === $arrayCallable->getMethod()) {
                return true;
            }
        }

        return false;
    }

    private function isEventSubscriberMethod(ClassMethod $classMethod, string $methodName): bool
    {
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return false;
        }

        if (! $this->nodeTypeResolver->isObjectType(
            $classNode,
            'Symfony\Component\EventDispatcher\EventSubscriberInterface')
        ) {
            return false;
        }

        $getSubscribedEventsClassMethod = $classNode->getMethod('getSubscribedEvents');
        if ($getSubscribedEventsClassMethod === null) {
            return false;
        }

        $methodNames = $this->eventSubscriberMethodNamesResolver->resolveFromClassMethod(
            $getSubscribedEventsClassMethod
        );

        return in_array($methodName, $methodNames, true);
    }
}
