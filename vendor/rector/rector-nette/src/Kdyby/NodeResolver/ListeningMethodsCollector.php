<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\NodeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Nette\Kdyby\Naming\EventClassNaming;
use Rector\Nette\Kdyby\ValueObject\EventClassAndClassMethod;
use Rector\Nette\Kdyby\ValueObject\NetteEventToContributeEventClass;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
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
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\Nette\Kdyby\Naming\EventClassNaming
     */
    private $eventClassNaming;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Nette\Kdyby\Naming\EventClassNaming $eventClassNaming, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->eventClassNaming = $eventClassNaming;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @return EventClassAndClassMethod[]
     */
    public function collectFromClassAndGetSubscribedEventClassMethod(\PhpParser\Node\Stmt\ClassMethod $getSubscribedEventsClassMethod, string $type) : array
    {
        /** @var Class_ $classLike */
        $classLike = $getSubscribedEventsClassMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        $this->eventClassesAndClassMethods = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $getSubscribedEventsClassMethod->stmts, function (\PhpParser\Node $node) use($classLike, $type) {
            $classMethod = $this->matchClassMethodByArrayItem($node, $classLike);
            if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return null;
            }
            if (!$node instanceof \PhpParser\Node\Expr\ArrayItem) {
                return;
            }
            if ($node->key === null) {
                return;
            }
            $eventClass = $this->valueResolver->getValue($node->key);
            if ($type === self::EVENT_TYPE_CONTRIBUTTE) {
                /** @var string $eventClass */
                $this->resolveContributeEventClassAndSubscribedClassMethod($eventClass, $classMethod);
                return null;
            }
            $eventClassAndClassMethod = $this->resolveCustomClassMethodAndEventClass($node, $classLike, $eventClass);
            if (!$eventClassAndClassMethod instanceof \Rector\Nette\Kdyby\ValueObject\EventClassAndClassMethod) {
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
    public function classMethodsListeningToEventClass(\PhpParser\Node\Stmt\ClassMethod $getSubscribedEventsClassMethod, string $type, string $eventClassName) : array
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
    private function matchClassMethodByArrayItem(\PhpParser\Node $node, \PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (!$node instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        if ($node->key === null) {
            return null;
        }
        return $this->matchClassMethodByNodeValue($class, $node->value);
    }
    private function resolveContributeEventClassAndSubscribedClassMethod(string $eventClass, \PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $contributeEventClasses = \Rector\Nette\Kdyby\ValueObject\NetteEventToContributeEventClass::PROPERTY_TO_EVENT_CLASS;
        if (!\in_array($eventClass, $contributeEventClasses, \true)) {
            return;
        }
        $this->eventClassesAndClassMethods[] = new \Rector\Nette\Kdyby\ValueObject\EventClassAndClassMethod($eventClass, $classMethod);
    }
    private function resolveCustomClassMethodAndEventClass(\PhpParser\Node\Expr\ArrayItem $arrayItem, \PhpParser\Node\Stmt\Class_ $class, string $eventClass) : ?\Rector\Nette\Kdyby\ValueObject\EventClassAndClassMethod
    {
        // custom method name
        $classMethodName = $this->valueResolver->getValue($arrayItem->value);
        $classMethod = $class->getMethod($classMethodName);
        if (\strpos($eventClass, '::') !== \false) {
            [$dispatchingClass, $property] = \explode('::', $eventClass);
            $eventClass = $this->eventClassNaming->createEventClassNameFromClassAndProperty($dispatchingClass, $property);
        }
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        return new \Rector\Nette\Kdyby\ValueObject\EventClassAndClassMethod($eventClass, $classMethod);
    }
    private function matchClassMethodByNodeValue(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $possibleMethodName = $this->valueResolver->getValue($expr);
        if (!\is_string($possibleMethodName)) {
            return null;
        }
        return $class->getMethod($possibleMethodName);
    }
}
