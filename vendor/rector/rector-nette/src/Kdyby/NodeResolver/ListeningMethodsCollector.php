<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\NodeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Nette\Kdyby\Naming\EventClassNaming;
use Rector\Nette\Kdyby\ValueObject\EventClassAndClassMethod;
use Rector\Nette\Kdyby\ValueObject\NetteEventToContributeEventClass;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
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
     * @var EventClassAndClassMethod[]
     */
    private $eventClassesAndClassMethods = [];
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Nette\Kdyby\Naming\EventClassNaming
     */
    private $eventClassNaming;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, EventClassNaming $eventClassNaming, ValueResolver $valueResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->eventClassNaming = $eventClassNaming;
        $this->valueResolver = $valueResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return EventClassAndClassMethod[]
     */
    public function collectFromClassAndGetSubscribedEventClassMethod(ClassMethod $getSubscribedEventsClassMethod, string $type) : array
    {
        $class = $this->betterNodeFinder->findParentType($getSubscribedEventsClassMethod, Class_::class);
        if (!$class instanceof Class_) {
            return [];
        }
        $this->eventClassesAndClassMethods = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $getSubscribedEventsClassMethod->stmts, function (Node $node) use($class, $type) {
            $classMethod = $this->matchClassMethodByArrayItem($node, $class);
            if (!$classMethod instanceof ClassMethod) {
                return null;
            }
            if (!$node instanceof ArrayItem) {
                return;
            }
            if ($node->key === null) {
                return;
            }
            $eventClass = $this->valueResolver->getValue($node->key);
            if (!\is_string($eventClass)) {
                throw new ShouldNotHappenException();
            }
            if ($type === self::EVENT_TYPE_CONTRIBUTTE) {
                /** @var string $eventClass */
                $this->resolveContributeEventClassAndSubscribedClassMethod($eventClass, $classMethod);
                return null;
            }
            $eventClassAndClassMethod = $this->resolveCustomClassMethodAndEventClass($node, $class, $eventClass);
            if (!$eventClassAndClassMethod instanceof EventClassAndClassMethod) {
                return null;
            }
            $this->eventClassesAndClassMethods[] = $eventClassAndClassMethod;
            return null;
        });
        return $this->eventClassesAndClassMethods;
    }
    /**
     * @return ClassMethod[]
     */
    public function classMethodsListeningToEventClass(ClassMethod $getSubscribedEventsClassMethod, string $type, string $eventClassName) : array
    {
        $eventClassesAndClassMethods = $this->collectFromClassAndGetSubscribedEventClassMethod($getSubscribedEventsClassMethod, $type);
        $classMethods = [];
        foreach ($eventClassesAndClassMethods as $eventClassAndClassMethod) {
            if ($eventClassAndClassMethod->getEventClass() !== $eventClassName) {
                continue;
            }
            $classMethods[] = $eventClassAndClassMethod->getClassMethod();
        }
        return $classMethods;
    }
    private function matchClassMethodByArrayItem(Node $node, Class_ $class) : ?ClassMethod
    {
        if (!$node instanceof ArrayItem) {
            return null;
        }
        if ($node->key === null) {
            return null;
        }
        return $this->matchClassMethodByNodeValue($class, $node->value);
    }
    private function resolveContributeEventClassAndSubscribedClassMethod(string $eventClass, ClassMethod $classMethod) : void
    {
        $contributeEventClasses = NetteEventToContributeEventClass::PROPERTY_TO_EVENT_CLASS;
        if (!\in_array($eventClass, $contributeEventClasses, \true)) {
            return;
        }
        $this->eventClassesAndClassMethods[] = new EventClassAndClassMethod($eventClass, $classMethod);
    }
    private function resolveCustomClassMethodAndEventClass(ArrayItem $arrayItem, Class_ $class, string $eventClass) : ?EventClassAndClassMethod
    {
        // custom method name
        $classMethodName = $this->valueResolver->getValue($arrayItem->value);
        if (!\is_string($classMethodName)) {
            throw new ShouldNotHappenException();
        }
        $classMethod = $class->getMethod($classMethodName);
        if (\strpos($eventClass, '::') !== \false) {
            [$dispatchingClass, $property] = \explode('::', $eventClass);
            $eventClass = $this->eventClassNaming->createEventClassNameFromClassAndProperty($dispatchingClass, $property);
        }
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        return new EventClassAndClassMethod($eventClass, $classMethod);
    }
    private function matchClassMethodByNodeValue(Class_ $class, Expr $expr) : ?ClassMethod
    {
        $possibleMethodName = $this->valueResolver->getValue($expr);
        if (!\is_string($possibleMethodName)) {
            return null;
        }
        return $class->getMethod($possibleMethodName);
    }
}
