<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony62\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Helper\MessengerHelper;
use Rector\Symfony\NodeAnalyzer\ClassAnalyzer;
use Rector\Symfony\NodeManipulator\ClassManipulator;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see Rector\Symfony\Tests\Symfony62\Rector\Class_\MessageSubscriberInterfaceToAttributeRector\MessageSubscriberInterfaceToAttributeRectorTest
 */
final class MessageSubscriberInterfaceToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private MessengerHelper $messengerHelper;
    /**
     * @readonly
     */
    private ClassManipulator $classManipulator;
    /**
     * @readonly
     */
    private ClassAnalyzer $classAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    private Class_ $subscriberClass;
    private string $newInvokeMethodName;
    public function __construct(MessengerHelper $messengerHelper, ClassManipulator $classManipulator, ClassAnalyzer $classAnalyzer, ValueResolver $valueResolver)
    {
        $this->messengerHelper = $messengerHelper;
        $this->classManipulator = $classManipulator;
        $this->classAnalyzer = $classAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace MessageSubscriberInterface with AsMessageHandler attribute(s)', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class SmsNotificationHandler implements MessageSubscriberInterface
{
    public function __invoke(SmsNotification $message)
    {
        // ...
    }

    public function handleOtherSmsNotification(OtherSmsNotification $message)
    {
        // ...
    }

    public static function getHandledMessages(): iterable
    {
        // handle this message on __invoke
        yield SmsNotification::class;

        // also handle this message on handleOtherSmsNotification
        yield OtherSmsNotification::class => [
            'method' => 'handleOtherSmsNotification',
            'priority' => 0,
            'bus' => 'messenger.bus.default',
        ];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class SmsNotificationHandler
{
    #[AsMessageHandler]
    public function handleSmsNotification(SmsNotification $message)
    {
        // ...
    }

    #[AsMessageHandler(priority: 0, bus: 'messenger.bus.default']
    public function handleOtherSmsNotification(OtherSmsNotification $message)
    {
        // ...
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
        if (!$this->classAnalyzer->hasImplements($node, MessengerHelper::MESSAGE_SUBSCRIBER_INTERFACE)) {
            return null;
        }
        $this->subscriberClass = $node;
        $getHandledMessagesClassMethod = $node->getMethod('getHandledMessages');
        if (!$getHandledMessagesClassMethod instanceof ClassMethod) {
            return null;
        }
        $stmts = (array) $getHandledMessagesClassMethod->stmts;
        if ($stmts === []) {
            return null;
        }
        if ($stmts[0] instanceof Expression && $stmts[0]->expr instanceof Yield_) {
            $this->handleYields($stmts);
        }
        $this->classManipulator->removeImplements($node, [MessengerHelper::MESSAGE_SUBSCRIBER_INTERFACE]);
        unset($node->stmts[$getHandledMessagesClassMethod->getAttribute(AttributeKey::STMT_KEY)]);
        return $node;
    }
    /**
     * @param array<int, Node\Stmt> $expressions
     */
    private function handleYields(array $expressions) : void
    {
        foreach ($expressions as $expression) {
            if (!$expression instanceof Expression || !$expression->expr instanceof Yield_) {
                continue;
            }
            $method = MethodName::INVOKE;
            $arguments = [];
            if ($expression->expr->key instanceof ClassConstFetch) {
                $array = $expression->expr->value;
                if (!$array instanceof Array_) {
                    continue;
                }
                $arguments = $this->parseArguments($array, $method);
                $this->addAttribute($method, $arguments);
                continue;
            }
            $value = $expression->expr->value;
            if (!$value instanceof ClassConstFetch || !$value->class instanceof Name) {
                continue;
            }
            $classParts = $value->class->getParts();
            $this->newInvokeMethodName = 'handle' . \end($classParts);
            $this->addAttribute($method, $arguments);
        }
    }
    /**
     * @return array<string, mixed>
     */
    private function parseArguments(Array_ $array, string &$method) : array
    {
        foreach ($array->items as $item) {
            if (!$item->value instanceof Expr) {
                continue;
            }
            $key = (string) $this->valueResolver->getValue($item->key);
            $value = $this->valueResolver->getValue($item->value);
            if ($key === 'method') {
                $method = $value;
                continue;
            }
            $arguments[$key] = $value;
        }
        return $arguments ?? [];
    }
    /**
     * @param array<string, mixed> $arguments
     */
    private function addAttribute(string $classMethodName, array $arguments) : void
    {
        $classMethod = $this->subscriberClass->getMethod($classMethodName);
        if (!$classMethod instanceof ClassMethod) {
            return;
        }
        if ($classMethodName === MethodName::INVOKE) {
            $this->renameInvoke($classMethod);
        }
        $this->messengerHelper->addAttribute($classMethod, $arguments);
    }
    private function renameInvoke(ClassMethod $classMethod) : void
    {
        $classMethod->name = new Identifier($this->newInvokeMethodName);
    }
}
