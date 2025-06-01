<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Symfony\ApplicationMetadata\ListenerServiceDefinitionProvider;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\NodeAnalyzer\ClassAnalyzer;
use Rector\Symfony\NodeFactory\GetSubscribedEventsClassMethodFactory;
use Rector\Symfony\ValueObject\EventNameToClassAndConstant;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Class_\EventListenerToEventSubscriberRector\EventListenerToEventSubscriberRectorTest
 */
final class EventListenerToEventSubscriberRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ListenerServiceDefinitionProvider $listenerServiceDefinitionProvider;
    /**
     * @readonly
     */
    private GetSubscribedEventsClassMethodFactory $getSubscribedEventsClassMethodFactory;
    /**
     * @readonly
     */
    private ClassAnalyzer $classAnalyzer;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private ClassNaming $classNaming;
    /**
     * @var string
     * @changelog https://regex101.com/r/qiHZ4T/1
     */
    private const LISTENER_MATCH_REGEX = '#^(.*?)(Listener)?$#';
    /**
     * @var EventNameToClassAndConstant[]
     */
    private array $eventNamesToClassConstants = [];
    public function __construct(ListenerServiceDefinitionProvider $listenerServiceDefinitionProvider, GetSubscribedEventsClassMethodFactory $getSubscribedEventsClassMethodFactory, ClassAnalyzer $classAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer, ClassNaming $classNaming)
    {
        $this->listenerServiceDefinitionProvider = $listenerServiceDefinitionProvider;
        $this->getSubscribedEventsClassMethodFactory = $getSubscribedEventsClassMethodFactory;
        $this->classAnalyzer = $classAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->classNaming = $classNaming;
        $this->eventNamesToClassConstants = [
            // kernel events
            new EventNameToClassAndConstant('kernel.request', SymfonyClass::KERNEL_EVENTS_CLASS, 'REQUEST'),
            new EventNameToClassAndConstant('kernel.exception', SymfonyClass::KERNEL_EVENTS_CLASS, 'EXCEPTION'),
            new EventNameToClassAndConstant('kernel.view', SymfonyClass::KERNEL_EVENTS_CLASS, 'VIEW'),
            new EventNameToClassAndConstant('kernel.controller', SymfonyClass::KERNEL_EVENTS_CLASS, 'CONTROLLER'),
            new EventNameToClassAndConstant('kernel.controller_arguments', SymfonyClass::KERNEL_EVENTS_CLASS, 'CONTROLLER_ARGUMENTS'),
            new EventNameToClassAndConstant('kernel.response', SymfonyClass::KERNEL_EVENTS_CLASS, 'RESPONSE'),
            new EventNameToClassAndConstant('kernel.terminate', SymfonyClass::KERNEL_EVENTS_CLASS, 'TERMINATE'),
            new EventNameToClassAndConstant('kernel.finish_request', SymfonyClass::KERNEL_EVENTS_CLASS, 'FINISH_REQUEST'),
            // console events
            new EventNameToClassAndConstant('console.command', SymfonyClass::CONSOLE_EVENTS_CLASS, 'COMMAND'),
            new EventNameToClassAndConstant('console.terminate', SymfonyClass::CONSOLE_EVENTS_CLASS, 'TERMINATE'),
            new EventNameToClassAndConstant('console.error', SymfonyClass::CONSOLE_EVENTS_CLASS, 'ERROR'),
        ];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        // there must be event dispatcher in the application
        $listenerClassesToEventsToMethods = $this->listenerServiceDefinitionProvider->extract();
        if ($listenerClassesToEventsToMethods === []) {
            return null;
        }
        $className = $this->getName($node);
        if (!isset($listenerClassesToEventsToMethods[$className])) {
            return null;
        }
        $this->changeListenerToSubscriberWithMethods($node, $listenerClassesToEventsToMethods[$className]);
        return $node;
    }
    /**
     * @param array<string, ServiceDefinition[]> $eventsToMethods
     */
    private function changeListenerToSubscriberWithMethods(Class_ $class, array $eventsToMethods) : void
    {
        $class->implements[] = new FullyQualified(SymfonyClass::EVENT_SUBSCRIBER_INTERFACE);
        $classShortName = $this->classNaming->getShortName($class);
        // remove suffix
        $classShortName = Strings::replace($classShortName, self::LISTENER_MATCH_REGEX, '$1');
        $class->name = new Identifier($classShortName . 'EventSubscriber');
        $classMethod = $this->getSubscribedEventsClassMethodFactory->createFromServiceDefinitionsAndEventsToMethods($eventsToMethods, $this->eventNamesToClassConstants);
        $class->stmts[] = $classMethod;
    }
    /**
     * @see https://symfony.com/doc/current/event_dispatcher.html#event-dispatcher_event-listener-attributes
     */
    private function hasAsListenerAttribute(Class_ $class) : bool
    {
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($class, SymfonyAttribute::AS_EVENT_LISTENER)) {
            return \true;
        }
        foreach ($class->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, SymfonyAttribute::AS_EVENT_LISTENER)) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        // anonymous class
        if ($class->isAnonymous()) {
            return \true;
        }
        // is already a subscriber
        if ($this->classAnalyzer->hasImplements($class, SymfonyClass::EVENT_SUBSCRIBER_INTERFACE)) {
            return \true;
        }
        return $this->hasAsListenerAttribute($class);
    }
}
