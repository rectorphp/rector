<?php declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Php\ValueObject\PhpVersionFeature;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;

/**
 * @see \Rector\SymfonyCodeQuality\Tests\Rector\Class_\EventListenerToEventSubscriberRector\EventListenerToEventSubscriberRectorTest
 */
final class EventListenerToEventSubscriberRector extends AbstractRector
{
    /**
     * @var string
     */
    private const EVENT_SUBSCRIBER_INTERFACE = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';

    /**
     * @var string
     */
    private const KERNEL_EVENTS_CLASS = 'Symfony\Component\HttpKernel\KernelEvents';

    /**
     * @var string
     */
    private const CONSOLE_EVENTS_CLASS = 'Symfony\Component\Console\ConsoleEvents';

    /**
     * @var string[][]
     */
    private $eventNamesToClassConstants = [
        // kernel events
        'kernel.request' => [self::KERNEL_EVENTS_CLASS, 'REQUEST'],
        'kernel.exception' => [self::KERNEL_EVENTS_CLASS, 'EXCEPTION'],
        'kernel.view' => [self::KERNEL_EVENTS_CLASS, 'VIEW'],
        'kernel.controller' => [self::KERNEL_EVENTS_CLASS, 'CONTROLLER'],
        'kernel.controller_arguments' => [self::KERNEL_EVENTS_CLASS, 'CONTROLLER_ARGUMENTS'],
        'kernel.response' => [self::KERNEL_EVENTS_CLASS, 'RESPONSE'],
        'kernel.terminate' => [self::KERNEL_EVENTS_CLASS, 'TERMINATE'],
        'kernel.finish_request' => [self::KERNEL_EVENTS_CLASS, 'FINISH_REQUEST'],
        // console events
        'console.command' => [self::CONSOLE_EVENTS_CLASS, 'COMMAND'],
        'console.terminate' => [self::CONSOLE_EVENTS_CLASS, 'TERMINATE'],
        'console.error' => [self::CONSOLE_EVENTS_CLASS, 'ERROR'],
    ];

    /**
     * @var AnalyzedApplicationContainerInterface
     */
    private $analyzedApplicationContainer;

    /**
     * @var mixed[][]
     */
    private $listenerClassesToEvents = [];

    /**
     * @var bool
     */
    private $areListenerClassesLoaded = false;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(
        AnalyzedApplicationContainerInterface $analyzedApplicationContainer,
        DocBlockManipulator $docBlockManipulator
    ) {
        $this->analyzedApplicationContainer = $analyzedApplicationContainer;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file',
            [
                new CodeSample(
                    <<<'PHP'
<?php

class SomeListener
{
     public function methodToBeCalled()
     {
     }
}

// in config.yaml
services:
    SomeListener:
        tags:
            - { name: kernel.event_listener, event: 'some_event', method: 'methodToBeCalled' }
PHP
                    ,
                    <<<'PHP'
<?php

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SomeEventSubscriber implements EventSubscriberInterface
{
     /**
      * @return string[]
      */
     public static function getSubscribedEvents(): array
     {
         return ['some_event' => 'methodToBeCalled'];  
     }

     public function methodToBeCalled()
     {
     }
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // anonymous class
        if ($node->name === null) {
            return null;
        }

        // is already a subscriber
        if ($this->isAlreadyEventSubscriber($node)) {
            return null;
        }

        // there must be event dispatcher in the application
        $listenerClassToEvents = $this->getListenerClassesToEventsToMethods();
        if ($listenerClassToEvents === []) {
            return null;
        }

        $className = $this->getName($node);
        if (! isset($listenerClassToEvents[$className])) {
            return null;
        }

        return $this->changeListenerToSubscriberWithMethods($node, $listenerClassToEvents[$className]);
    }

    private function isAlreadyEventSubscriber(Class_ $class): bool
    {
        foreach ((array) $class->implements as $implement) {
            if ($this->isName($implement, 'Symfony\Component\EventDispatcher\EventSubscriberInterface')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[][]
     */
    private function getListenerClassesToEventsToMethods(): array
    {
        if ($this->areListenerClassesLoaded) {
            return $this->listenerClassesToEvents;
        }

        if (! $this->analyzedApplicationContainer->hasService('event_dispatcher')) {
            $this->areListenerClassesLoaded = true;

            return [];
        }

        /** @var TraceableEventDispatcher $applicationEventDispatcher */
        $applicationEventDispatcher = $this->analyzedApplicationContainer->getService('event_dispatcher');

        foreach ($applicationEventDispatcher->getListeners() as $eventName => $listenersInEvent) {
            foreach ($listenersInEvent as $listener) {
                // must be array → class and method
                if (! is_array($listener)) {
                    continue;
                }

                $listenerClass = get_class($listener[0]);

                // skip Symfony core listeners
                if (Strings::match($listenerClass, '#^(Symfony|Sensio|Doctrine)\\\\#')) {
                    continue;
                }

                $listenerPriority = $applicationEventDispatcher->getListenerPriority($eventName, $listener);

                // group event name - method - class :)
                $this->listenerClassesToEvents[$listenerClass][$eventName][] = [$listener[1], $listenerPriority];
            }
        }

        $this->areListenerClassesLoaded = true;

        return $this->listenerClassesToEvents;
    }

    /**
     * @param mixed[] $eventsToMethods
     */
    private function changeListenerToSubscriberWithMethods(Class_ $class, array $eventsToMethods): Class_
    {
        $class->implements[] = new FullyQualified(self::EVENT_SUBSCRIBER_INTERFACE);

        $classShortName = (string) $class->name;
        // remove suffix
        $classShortName = Strings::replace($classShortName, '#^(.*?)(Listener)?$#', '$1');
        $class->name = new Identifier($classShortName . 'EventSubscriber');

        $clasMethod = $this->createGetSubscribedEventsClassMethod($eventsToMethods);
        $class->stmts[] = $clasMethod;

        return $class;
    }

    /**
     * @return String_|ClassConstFetch
     */
    private function createEventName(string $eventName): Node
    {
        if (class_exists($eventName)) {
            return $this->createClassConstantReference($eventName);
        }

        // is string a that could be caught in constant, e.g. KernelEvents?
        if (isset($this->eventNamesToClassConstants[$eventName])) {
            [$class, $constant] = $this->eventNamesToClassConstants[$eventName];

            return $this->createClassConstant($class, $constant);
        }

        return new String_($eventName);
    }

    /**
     * @param mixed[][] $eventsToMethods
     */
    private function createGetSubscribedEventsClassMethod(array $eventsToMethods): ClassMethod
    {
        $getSubscribedEventsMethod = $this->nodeFactory->createPublicMethod('getSubscribedEvents');

        $eventsToMethodsArray = new Array_();

        $this->makeStatic($getSubscribedEventsMethod);

        foreach ($eventsToMethods as $eventName => $methodNamesWithPriorities) {
            $eventName = $this->createEventName($eventName);

            if (count($methodNamesWithPriorities) === 1) {
                $this->createSingleMethod($methodNamesWithPriorities, $eventName, $eventsToMethodsArray);
            } else {
                $this->createMultipleMethods($methodNamesWithPriorities, $eventName, $eventsToMethodsArray);
            }
        }

        $getSubscribedEventsMethod->stmts[] = new Return_($eventsToMethodsArray);
        $this->decorateClassMethodWithReturnType($getSubscribedEventsMethod);

        return $getSubscribedEventsMethod;
    }

    private function decorateClassMethodWithReturnType(ClassMethod $classMethod): void
    {
        if ($this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new Identifier('array');
        }

        $arrayMixedType = new ArrayType(new MixedType(), new MixedType(true));
        $this->docBlockManipulator->addReturnTag($classMethod, $arrayMixedType);
    }

    /**
     * @param ClassConstFetch|String_ $expr
     * @param mixed[] $methodNamesWithPriorities
     */
    private function createSingleMethod(
        array $methodNamesWithPriorities,
        Expr $expr,
        Array_ $eventsToMethodsArray
    ): void {
        [$methodName, $priority] = $methodNamesWithPriorities[0];

        if ($priority) {
            $methodNameWithPriorityArray = new Array_();
            $methodNameWithPriorityArray->items[] = new ArrayItem(new String_($methodName));
            $methodNameWithPriorityArray->items[] = new ArrayItem(new LNumber((int) $priority));

            $eventsToMethodsArray->items[] = new ArrayItem($methodNameWithPriorityArray, $expr);
        } else {
            $eventsToMethodsArray->items[] = new ArrayItem(new String_($methodName), $expr);
        }
    }

    /**
     * @param ClassConstFetch|String_ $expr
     * @param mixed[] $methodNamesWithPriorities
     */
    private function createMultipleMethods(
        array $methodNamesWithPriorities,
        Expr $expr,
        Array_ $eventsToMethodsArray
    ): void {
        $multipleMethodsArray = new Array_();

        foreach ($methodNamesWithPriorities as $methodNamesWithPriority) {
            [$methodName, $priority] = $methodNamesWithPriority;

            if ($priority) {
                $methodNameWithPriorityArray = new Array_();
                $methodNameWithPriorityArray->items[] = new ArrayItem(new String_($methodName));
                $methodNameWithPriorityArray->items[] = new ArrayItem(new LNumber((int) $priority));

                $multipleMethodsArray->items[] = new ArrayItem($methodNameWithPriorityArray);
            } else {
                $multipleMethodsArray->items[] = new ArrayItem(new String_($methodName));
            }
        }

        $eventsToMethodsArray->items[] = new ArrayItem($multipleMethodsArray, $expr);
    }
}
