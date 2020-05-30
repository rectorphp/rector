<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NetteKdyby\Naming\EventClassNaming;
use Rector\NetteKdyby\ValueObject\NetteEventToContributeEventClass;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ListeningMethodsCollector
{
    /**
     * @var string
     */
    public const EVENT_TYPE_CONTRIBUTTE = 'contributte';

    /**
     * @var string
     */
    public const EVENT_TYPE_CUSTOM = 'custom';

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var EventClassNaming
     */
    private $eventClassNaming;

    /**
     * @var array<string, ClassMethod>
     */
    private $classMethodsByEventClass = [];

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        ValueResolver $valueResolver,
        EventClassNaming $eventClassNaming
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->valueResolver = $valueResolver;
        $this->eventClassNaming = $eventClassNaming;
    }

    /**
     * @return array<string, ClassMethod>
     */
    public function collectFromClassAndGetSubscribedEventClassMethod(
        ClassMethod $getSubscribedEventsClassMethod,
        string $type,
        ?string $eventClassName = null
    ): array {
        /** @var Class_ $class */
        $class = $getSubscribedEventsClassMethod->getAttribute(AttributeKey::CLASS_NODE);

        $this->classMethodsByEventClass = [];

        $this->callableNodeTraverser->traverseNodesWithCallable(
            (array) $getSubscribedEventsClassMethod->stmts,
            function (Node $node) use ($class, $type) {
                if (! $node instanceof ArrayItem) {
                    return null;
                }

                if ($node->key === null) {
                    return null;
                }

                $classMethod = $this->matchClassMethodByNodeValue($class, $node->value);
                if ($classMethod === null) {
                    return null;
                }

                $eventClass = $this->valueResolver->getValue($node->key);

                if ($type === self::EVENT_TYPE_CONTRIBUTTE) {
                    /** @var string $eventClass */
                    $this->resolveContributeEventClassAndSubscribedClassMethod($eventClass, $classMethod);
                    return;
                }

                [$classMethod,
            $eventClass] = $this->resolveCustomClassMethodAndEventClass($node, $class, $eventClass);
                if ($classMethod === null) {
                    return null;
                }

                if (! is_string($eventClass)) {
                    return null;
                }

                if (isset($this->classMethodsByEventClass[$eventClass])) {
                    throw new ShouldNotHappenException(sprintf('"%s" class already has its class method', $eventClass));
                }

                $this->classMethodsByEventClass[$eventClass] = $classMethod;
            }
        );

        if ($eventClassName) {
            return isset($this->classMethodsByEventClass[$eventClassName]) ? [
                $this->classMethodsByEventClass[$eventClassName],
            ] : [];
        }

        return $this->classMethodsByEventClass;
    }

    private function resolveCustomClassMethodAndEventClass(
        ArrayItem $arrayItem,
        Class_ $class,
        string $eventClass
    ): array {
        // custom method name
        $classMethodName = $this->valueResolver->getValue($arrayItem->value);
        $classMethod = $class->getMethod($classMethodName);

        if (Strings::contains($eventClass, '::')) {
            [$dispatchingClass, $property] = Strings::split($eventClass, '#::#');
            $eventClass = $this->eventClassNaming->createEventClassNameFromClassAndProperty(
                $dispatchingClass,
                $property
            );
        }

        return [$classMethod, $eventClass];
    }

    private function matchClassMethodByNodeValue(Class_ $class, Expr $expr): ?ClassMethod
    {
        $possibleMethodName = $this->valueResolver->getValue($expr);
        if (! is_string($possibleMethodName)) {
            return null;
        }

        return $class->getMethod($possibleMethodName);
    }

    private function resolveContributeEventClassAndSubscribedClassMethod(
        string $eventClass,
        ClassMethod $classMethod
    ): void {
        $contributeEventClasses = NetteEventToContributeEventClass::PROPERTY_TO_EVENT_CLASS;

        if (! in_array($eventClass, $contributeEventClasses, true)) {
            return;
        }

        $this->classMethodsByEventClass[$eventClass] = $classMethod;
    }
}
