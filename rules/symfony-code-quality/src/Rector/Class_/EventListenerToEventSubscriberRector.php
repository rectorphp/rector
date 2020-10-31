<?php

declare(strict_types=1);

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
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Symfony\Contract\Tag\TagInterface;
use Rector\Symfony\ServiceMapProvider;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;
use Rector\SymfonyCodeQuality\ValueObject\EventNameToClassAndConstant;

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
     * @var string
     * @see https://regex101.com/r/qiHZ4T/1
     */
    private const LISTENER_MATCH_REGEX = '#^(.*?)(Listener)?$#';

    /**
     * @var string
     * @see https://regex101.com/r/j6SAga/1
     */
    private const SYMFONY_FAMILY_REGEX = '#^(Symfony|Sensio|Doctrine)\b#';

    /**
     * @var bool
     */
    private $areListenerClassesLoaded = false;

    /**
     * @var EventNameToClassAndConstant[]
     */
    private $eventNamesToClassConstants = [];

    /**
     * @var ServiceDefinition[][][]
     */
    private $listenerClassesToEvents = [];

    /**
     * @var ServiceMapProvider
     */
    private $applicationServiceMapProvider;

    public function __construct(ServiceMapProvider $applicationServiceMapProvider)
    {
        $this->applicationServiceMapProvider = $applicationServiceMapProvider;

        $this->eventNamesToClassConstants = [
            // kernel events
            new EventNameToClassAndConstant('kernel.request', self::KERNEL_EVENTS_CLASS, 'REQUEST'),
            new EventNameToClassAndConstant('kernel.exception', self::KERNEL_EVENTS_CLASS, 'EXCEPTION'),
            new EventNameToClassAndConstant('kernel.view', self::KERNEL_EVENTS_CLASS, 'VIEW'),
            new EventNameToClassAndConstant('kernel.controller', self::KERNEL_EVENTS_CLASS, 'CONTROLLER'),
            new EventNameToClassAndConstant(
                'kernel.controller_arguments',
                self::KERNEL_EVENTS_CLASS,
                'CONTROLLER_ARGUMENTS'
            ),
            new EventNameToClassAndConstant('kernel.response', self::KERNEL_EVENTS_CLASS, 'RESPONSE'),
            new EventNameToClassAndConstant('kernel.terminate', self::KERNEL_EVENTS_CLASS, 'TERMINATE'),
            new EventNameToClassAndConstant('kernel.finish_request', self::KERNEL_EVENTS_CLASS, 'FINISH_REQUEST'),
            // console events
            new EventNameToClassAndConstant('console.command', self::CONSOLE_EVENTS_CLASS, 'COMMAND'),
            new EventNameToClassAndConstant('console.terminate', self::CONSOLE_EVENTS_CLASS, 'TERMINATE'),
            new EventNameToClassAndConstant('console.error', self::CONSOLE_EVENTS_CLASS, 'ERROR'),
        ];
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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
        $listenerClassesToEventsToMethods = $this->getListenerClassesToEventsToMethods();
        if ($listenerClassesToEventsToMethods === []) {
            return null;
        }

        $className = $this->getName($node);
        if (! isset($listenerClassesToEventsToMethods[$className])) {
            return null;
        }

        return $this->changeListenerToSubscriberWithMethods($node, $listenerClassesToEventsToMethods[$className]);
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
     * @return ServiceDefinition[][][]
     */
    private function getListenerClassesToEventsToMethods(): array
    {
        if ($this->areListenerClassesLoaded) {
            return $this->listenerClassesToEvents;
        }

        $serviceMap = $this->applicationServiceMapProvider->provide();
        $eventListeners = $serviceMap->getServicesByTag('kernel.event_listener');

        foreach ($eventListeners as $eventListener) {
            // skip Symfony core listeners
            if (Strings::match((string) $eventListener->getClass(), self::SYMFONY_FAMILY_REGEX)) {
                continue;
            }

            foreach ($eventListener->getTags() as $tag) {
                if (! $tag instanceof EventListenerTag) {
                    continue;
                }

                $eventName = $tag->getEvent();
                $this->listenerClassesToEvents[$eventListener->getClass()][$eventName][] = $eventListener;
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
        $classShortName = Strings::replace($classShortName, self::LISTENER_MATCH_REGEX, '$1');

        $class->name = new Identifier($classShortName . 'EventSubscriber');

        $classMethod = $this->createGetSubscribedEventsClassMethod($eventsToMethods);
        $class->stmts[] = $classMethod;

        return $class;
    }

    /**
     * @param mixed[][] $eventsToMethods
     */
    private function createGetSubscribedEventsClassMethod(array $eventsToMethods): ClassMethod
    {
        $getSubscribersClassMethod = $this->nodeFactory->createPublicMethod('getSubscribedEvents');

        $eventsToMethodsArray = new Array_();

        $this->makeStatic($getSubscribersClassMethod);

        foreach ($eventsToMethods as $eventName => $methodNamesWithPriorities) {
            $eventNameExpr = $this->createEventName($eventName);

            if (count($methodNamesWithPriorities) === 1) {
                $this->createSingleMethod($methodNamesWithPriorities, $eventNameExpr, $eventsToMethodsArray);
            } else {
                $this->createMultipleMethods(
                    $methodNamesWithPriorities,
                    $eventNameExpr,
                    $eventsToMethodsArray,
                    $eventName
                );
            }
        }

        $getSubscribersClassMethod->stmts[] = new Return_($eventsToMethodsArray);
        $this->decorateClassMethodWithReturnType($getSubscribersClassMethod);

        return $getSubscribersClassMethod;
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
        foreach ($this->eventNamesToClassConstants as $eventNameToClassConstant) {
            if ($eventNameToClassConstant->getEventName() !== $eventName) {
                continue;
            }

            return $this->createClassConstFetch(
                $eventNameToClassConstant->getEventClass(),
                $eventNameToClassConstant->getEventConstant()
            );
        }

        return new String_($eventName);
    }

    /**
     * @param ClassConstFetch|String_ $expr
     * @param ServiceDefinition[] $methodNamesWithPriorities
     */
    private function createSingleMethod(
        array $methodNamesWithPriorities,
        Expr $expr,
        Array_ $eventsToMethodsArray
    ): void {

        /** @var EventListenerTag $eventTag */
        $eventTag = $methodNamesWithPriorities[0]->getTags()[0];

        $methodName = $eventTag->getMethod();
        $priority = $eventTag->getPriority();

        if ($priority !== 0) {
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
     * @param ServiceDefinition[] $methodNamesWithPriorities
     */
    private function createMultipleMethods(
        array $methodNamesWithPriorities,
        Expr $expr,
        Array_ $eventsToMethodsArray,
        string $eventName
    ): void {
        $eventItems = [];
        $alreadyUsedTags = [];

        foreach ($methodNamesWithPriorities as $methodNamesWithPriority) {
            foreach ($methodNamesWithPriority->getTags() as $tag) {
                if ($this->shouldSkip($eventName, $tag, $alreadyUsedTags)) {
                    continue;
                }

                $eventItems[] = $this->createEventItem($tag);

                $alreadyUsedTags[] = $tag;
            }
        }

        $multipleMethodsArray = new Array_($eventItems);

        $eventsToMethodsArray->items[] = new ArrayItem($multipleMethodsArray, $expr);
    }

    private function decorateClassMethodWithReturnType(ClassMethod $classMethod): void
    {
        if ($this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new Identifier('array');
        }

        $returnType = new ArrayType(new MixedType(), new MixedType(true));
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        $phpDocInfo->changeReturnType($returnType);
    }

    /**
     * @param TagInterface[] $alreadyUsedTags
     */
    private function shouldSkip(string $eventName, TagInterface $tag, array $alreadyUsedTags): bool
    {
        if (! $tag instanceof EventListenerTag) {
            return true;
        }

        if ($eventName !== $tag->getEvent()) {
            return true;
        }

        return in_array($tag, $alreadyUsedTags, true);
    }

    private function createEventItem(EventListenerTag $eventListenerTag): ArrayItem
    {
        if ($eventListenerTag->getPriority() !== 0) {
            $methodNameWithPriorityArray = new Array_();
            $methodNameWithPriorityArray->items[] = new ArrayItem(new String_($eventListenerTag->getMethod()));
            $methodNameWithPriorityArray->items[] = new ArrayItem(new LNumber($eventListenerTag->getPriority()));

            return new ArrayItem($methodNameWithPriorityArray);
        }

        return new ArrayItem(new String_($eventListenerTag->getMethod()));
    }
}
