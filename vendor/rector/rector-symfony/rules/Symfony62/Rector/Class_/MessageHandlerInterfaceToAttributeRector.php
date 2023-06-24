<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony62\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Symfony\Helper\MessengerHelper;
use Rector\Symfony\NodeAnalyzer\ClassAnalyzer;
use Rector\Symfony\NodeManipulator\ClassManipulator;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony62\Rector\Class_\MessageHandlerInterfaceToAttributeRector\MessageHandlerToAttributeRectorTest
 */
final class MessageHandlerInterfaceToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Symfony\Helper\MessengerHelper
     */
    private $messengerHelper;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeManipulator\ClassManipulator
     */
    private $classManipulator;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(MessengerHelper $messengerHelper, ClassManipulator $classManipulator, ClassAnalyzer $classAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->messengerHelper = $messengerHelper;
        $this->classManipulator = $classManipulator;
        $this->classAnalyzer = $classAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces MessageHandlerInterface with AsMessageHandler attribute', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SmsNotificationHandler implements MessageHandlerInterface
{
    public function __invoke(SmsNotification $message)
    {
        // ... do some work - like sending an SMS message!
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SmsNotificationHandler
{
    public function __invoke(SmsNotification $message)
    {
        // ... do some work - like sending an SMS message!
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
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($node, MessengerHelper::AS_MESSAGE_HANDLER_ATTRIBUTE)) {
            return null;
        }
        if (!$this->classAnalyzer->hasImplements($node, MessengerHelper::MESSAGE_HANDLER_INTERFACE)) {
            $handlers = $this->messengerHelper->getHandlersFromServices();
            if ($handlers === []) {
                return null;
            }
            return $this->checkForServices($node, $handlers);
        }
        $this->classManipulator->removeImplements($node, [MessengerHelper::MESSAGE_HANDLER_INTERFACE]);
        return $this->messengerHelper->addAttribute($node);
    }
    /**
     * @param ServiceDefinition[] $handlers
     */
    private function checkForServices(Class_ $class, array $handlers) : Class_
    {
        foreach ($handlers as $handler) {
            if ($this->isName($class, $handler->getClass() ?? $handler->getId())) {
                $options = $this->messengerHelper->extractOptionsFromServiceDefinition($handler);
                if (!isset($options['method']) || $options['method'] === '__invoke') {
                    $this->messengerHelper->addAttribute($class, $options);
                }
            }
        }
        return $class;
    }
}
