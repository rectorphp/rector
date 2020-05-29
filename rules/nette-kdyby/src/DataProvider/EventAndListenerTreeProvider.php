<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\DataProvider;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\NetteKdyby\Naming\EventClassNaming;
use Rector\NetteKdyby\NodeFactory\DispatchMethodCallFactory;
use Rector\NetteKdyby\NodeFactory\EventValueObjectClassFactory;
use Rector\NetteKdyby\NodeResolver\ListeningMethodsCollector;
use Rector\NetteKdyby\ValueObject\EventAndListenerTree;
use Rector\NetteKdyby\ValueObject\GetterMethodBlueprint;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class EventAndListenerTreeProvider
{
    /**
     * @var OnPropertyMagicCallProvider
     */
    private $onPropertyMagicCallProvider;

    /**
     * @var ListeningMethodsCollector
     */
    private $listeningMethodsCollector;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var EventClassNaming
     */
    private $eventClassNaming;

    /**
     * @var EventAndListenerTree[]
     */
    private $eventAndListenerTrees = [];

    /**
     * @var EventValueObjectClassFactory
     */
    private $eventValueObjectClassFactory;

    /**
     * @var DispatchMethodCallFactory
     */
    private $dispatchMethodCallFactory;

    /**
     * @var GetSubscribedEventsClassMethodProvider
     */
    private $getSubscribedEventsClassMethodProvider;

    public function __construct(
        OnPropertyMagicCallProvider $onPropertyMagicCallProvider,
        ListeningMethodsCollector $listeningMethodsCollector,
        NodeNameResolver $nodeNameResolver,
        EventClassNaming $eventClassNaming,
        EventValueObjectClassFactory $eventValueObjectClassFactory,
        DispatchMethodCallFactory $dispatchMethodCallFactory,
        GetSubscribedEventsClassMethodProvider $getSubscribedEventsClassMethodProvider
    ) {
        $this->onPropertyMagicCallProvider = $onPropertyMagicCallProvider;
        $this->listeningMethodsCollector = $listeningMethodsCollector;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->eventClassNaming = $eventClassNaming;
        $this->eventValueObjectClassFactory = $eventValueObjectClassFactory;
        $this->dispatchMethodCallFactory = $dispatchMethodCallFactory;
        $this->getSubscribedEventsClassMethodProvider = $getSubscribedEventsClassMethodProvider;
    }

    public function matchMethodCall(MethodCall $methodCall): ?EventAndListenerTree
    {
        $this->initializeEventAndListenerTrees();

        foreach ($this->eventAndListenerTrees as $eventAndListenerTree) {
            if ($eventAndListenerTree->getMagicDispatchMethodCall() !== $methodCall) {
                continue;
            }

            return $eventAndListenerTree;
        }

        return null;
    }

    /**
     * @return EventAndListenerTree[]
     */
    public function provide(): array
    {
        $this->initializeEventAndListenerTrees();

        return $this->eventAndListenerTrees;
    }

    private function resolveMagicProperty(MethodCall $methodCall): ?Property
    {
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($methodCall->name);

        /** @var Class_ $class */
        $class = $methodCall->getAttribute(AttributeKey::CLASS_NODE);

        return $class->getProperty($methodName);
    }

    private function initializeEventAndListenerTrees(): void
    {
        if ($this->eventAndListenerTrees !== [] && ! StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return;
        }

        $this->eventAndListenerTrees = [];

        foreach ($this->onPropertyMagicCallProvider->provide() as $methodCall) {
            $magicProperty = $this->resolveMagicProperty($methodCall);

            $eventClassName = $this->eventClassNaming->createEventClassNameFromMethodCall($methodCall);

            $eventFileLocation = $this->eventClassNaming->resolveEventFileLocationFromMethodCall($methodCall);

            $eventClassInNamespace = $this->eventValueObjectClassFactory->create(
                $eventClassName,
                (array) $methodCall->args
            );

            $dispatchMethodCall = $this->dispatchMethodCallFactory->createFromEventClassName($eventClassName);

            $listeningClassMethodsByClass = $this->getListeningClassMethodsInEventSubscriberByClass($eventClassName);

            // getter names by variable name and type
            $getterNamesWithReturnTypeAndVariableName = $this->resolveGetterNamesWithReturnTypeAndVariableName(
                $eventClassInNamespace
            );

            $eventAndListenerTree = new EventAndListenerTree(
                $methodCall,
                $magicProperty,
                $eventClassName,
                $eventFileLocation,
                $eventClassInNamespace,
                $dispatchMethodCall,
                $listeningClassMethodsByClass,
                $getterNamesWithReturnTypeAndVariableName
            );

            $this->eventAndListenerTrees[] = $eventAndListenerTree;
        }
    }

    /**
     * @return ClassMethod[][]
     */
    private function getListeningClassMethodsInEventSubscriberByClass(string $eventClassName): array
    {
        $listeningClassMethodsByClass = [];

        foreach ($this->getSubscribedEventsClassMethodProvider->provide() as $getSubscribedClassMethod) {
            /** @var string $className */
            $className = $getSubscribedClassMethod->getAttribute(AttributeKey::CLASS_NAME);

            $currentListeningClassMethods = $this->listeningMethodsCollector->collectFromClassAndGetSubscribedEventClassMethod(
                $getSubscribedClassMethod,
                ListeningMethodsCollector::EVENT_TYPE_CUSTOM,
                $eventClassName
            );

            $listeningClassMethodsByClass[$className] = $currentListeningClassMethods;
        }

        return $listeningClassMethodsByClass;
    }

    private function resolveGetterNamesWithReturnTypeAndVariableName(Namespace_ $eventClassInNamespace): array
    {
        /** @var Class_ $eventClass */
        $eventClass = $eventClassInNamespace->stmts[0];
        $getterNamesWithReturnTypeAndVariableName = [];
        foreach ($eventClass->getMethods() as $classMethod) {
            if (! $this->nodeNameResolver->isName($classMethod, 'get*')) {
                continue;
            }

            /** @var Return_ $return */
            $return = $classMethod->stmts[0];
            /** @var PropertyFetch $propertyFetch */
            $propertyFetch = $return->expr;

            $classMethodName = $this->nodeNameResolver->getName($classMethod);

            /** @var string $variableName */
            $variableName = $this->nodeNameResolver->getName($propertyFetch->name);

            $getterNamesWithReturnTypeAndVariableName[] = new GetterMethodBlueprint(
                $classMethodName,
                $classMethod->returnType,
                $variableName
            );
        }

        return $getterNamesWithReturnTypeAndVariableName;
    }
}
