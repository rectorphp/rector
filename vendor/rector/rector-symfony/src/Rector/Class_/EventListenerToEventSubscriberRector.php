<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Class_;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\ApplicationMetadata\ListenerServiceDefinitionProvider;
use Rector\Symfony\NodeFactory\GetSubscribedEventsClassMethodFactory;
use Rector\Symfony\ValueObject\EventNameToClassAndConstant;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\Class_\EventListenerToEventSubscriberRector\EventListenerToEventSubscriberRectorTest
 */
final class EventListenerToEventSubscriberRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const EVENT_SUBSCRIBER_INTERFACE = 'Symfony\\Component\\EventDispatcher\\EventSubscriberInterface';
    /**
     * @var string
     */
    private const KERNEL_EVENTS_CLASS = 'Symfony\\Component\\HttpKernel\\KernelEvents';
    /**
     * @var string
     */
    private const CONSOLE_EVENTS_CLASS = 'Symfony\\Component\\Console\\ConsoleEvents';
    /**
     * @var string
     * @see https://regex101.com/r/qiHZ4T/1
     */
    private const LISTENER_MATCH_REGEX = '#^(.*?)(Listener)?$#';
    /**
     * @var EventNameToClassAndConstant[]
     */
    private $eventNamesToClassConstants = [];
    /**
     * @var \Rector\Symfony\ApplicationMetadata\ListenerServiceDefinitionProvider
     */
    private $listenerServiceDefinitionProvider;
    /**
     * @var \Rector\Symfony\NodeFactory\GetSubscribedEventsClassMethodFactory
     */
    private $getSubscribedEventsClassMethodFactory;
    public function __construct(\Rector\Symfony\ApplicationMetadata\ListenerServiceDefinitionProvider $listenerServiceDefinitionProvider, \Rector\Symfony\NodeFactory\GetSubscribedEventsClassMethodFactory $getSubscribedEventsClassMethodFactory)
    {
        $this->listenerServiceDefinitionProvider = $listenerServiceDefinitionProvider;
        $this->getSubscribedEventsClassMethodFactory = $getSubscribedEventsClassMethodFactory;
        $this->eventNamesToClassConstants = [
            // kernel events
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('kernel.request', self::KERNEL_EVENTS_CLASS, 'REQUEST'),
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('kernel.exception', self::KERNEL_EVENTS_CLASS, 'EXCEPTION'),
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('kernel.view', self::KERNEL_EVENTS_CLASS, 'VIEW'),
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('kernel.controller', self::KERNEL_EVENTS_CLASS, 'CONTROLLER'),
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('kernel.controller_arguments', self::KERNEL_EVENTS_CLASS, 'CONTROLLER_ARGUMENTS'),
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('kernel.response', self::KERNEL_EVENTS_CLASS, 'RESPONSE'),
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('kernel.terminate', self::KERNEL_EVENTS_CLASS, 'TERMINATE'),
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('kernel.finish_request', self::KERNEL_EVENTS_CLASS, 'FINISH_REQUEST'),
            // console events
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('console.command', self::CONSOLE_EVENTS_CLASS, 'COMMAND'),
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('console.terminate', self::CONSOLE_EVENTS_CLASS, 'TERMINATE'),
            new \Rector\Symfony\ValueObject\EventNameToClassAndConstant('console.error', self::CONSOLE_EVENTS_CLASS, 'ERROR'),
        ];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change Symfony Event listener class to Event Subscriber based on configuration in service.yaml file', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
    private function isAlreadyEventSubscriber(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        foreach ($class->implements as $implement) {
            if ($this->isName($implement, 'Symfony\\Component\\EventDispatcher\\EventSubscriberInterface')) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param array<string, ServiceDefinition[]> $eventsToMethods
     */
    private function changeListenerToSubscriberWithMethods(\PhpParser\Node\Stmt\Class_ $class, array $eventsToMethods) : void
    {
        $class->implements[] = new \PhpParser\Node\Name\FullyQualified(self::EVENT_SUBSCRIBER_INTERFACE);
        $classShortName = $this->nodeNameResolver->getShortName($class);
        // remove suffix
        $classShortName = \RectorPrefix20211020\Nette\Utils\Strings::replace($classShortName, self::LISTENER_MATCH_REGEX, '$1');
        $class->name = new \PhpParser\Node\Identifier($classShortName . 'EventSubscriber');
        $classMethod = $this->getSubscribedEventsClassMethodFactory->createFromServiceDefinitionsAndEventsToMethods($eventsToMethods, $this->eventNamesToClassConstants);
        $class->stmts[] = $classMethod;
    }
}
