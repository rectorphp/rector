<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteKdyby\Naming\EventClassNaming;
use Rector\NetteKdyby\NodeFactory\CustomEventFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @see \Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\ReplaceMagicPropertyEventWithEventClassRectorTest
 */
final class ReplaceMagicPropertyEventWithEventClassRector extends AbstractRector
{
    /**
     * @var EventClassNaming
     */
    private $eventClassNaming;

    /**
     * @var CustomEventFactory
     */
    private $customEventFactory;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(
        EventClassNaming $eventClassNaming,
        CustomEventFactory $customEventFactory,
        ClassNaming $classNaming
    ) {
        $this->eventClassNaming = $eventClassNaming;
        $this->customEventFactory = $customEventFactory;
        $this->classNaming = $classNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change $onProperty magic call with event disptacher and class dispatch', [
            new CodeSample(
                <<<'PHP'
final class FileManager
{
    public $onUpload;

    public function run(User $user)
    {
        $this->onUpload($user);
    }
}
PHP
,
                <<<'PHP'
final class FileManager
{
    use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run(User $user)
    {
        $onFileManagerUploadEvent = new FileManagerUploadEvent($user);
        $this->eventDispatcher->dispatch($onFileManagerUploadEvent);
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // 1. is onProperty? call
        if (! $this->isLocalOnPropertyCall($node)) {
            return null;
        }

        // 2. guess event name
        $eventClassName = $this->createEventClassName($node);
        $eventFileLocation = $this->eventClassNaming->resolveEventFileLocation($node);

        // 3. create new event class with args
        $eventClass = $this->customEventFactory->create($eventClassName, (array) $node->args);
        $this->printToFile($eventClass, $eventFileLocation);

        // 4. ad disatch method call
        $dispatchMethodCall = $this->createDispatchMethodCall($eventClassName);
        $this->addNodeAfterNode($dispatchMethodCall, $node);

        // 5. return evnet addign
        // add event dispathcer depdency if needed
        $assign = $this->createEventInstanceAssign($eventClassName, $node);

        /** @var Class_ $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        $this->addPropertyToClass($class, new FullyQualifiedObjectType(EventDispatcher::class), 'eventDispatcher');

        // 6. remove property
        $this->removeMagicProperty($node);

        return $assign;
    }

    private function isLocalOnPropertyCall(MethodCall $methodCall): bool
    {
        if (! $this->isName($methodCall->var, 'this')) {
            return false;
        }

        if (! $this->isName($methodCall->name, 'on*')) {
            return false;
        }

        $methodName = $this->getName($methodCall->name);
        if ($methodName === null) {
            return false;
        }

        $className = $methodCall->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        if (method_exists($className, $methodName)) {
            return false;
        }

        return property_exists($className, $methodName);
    }

    /**
     * "App\SomeNamespace\SomeClass"
     * ↓
     * "App\SomeNamespace\Event\SomeClassUploadEvent"
     */
    private function createEventClassName(MethodCall $methodCall): string
    {
        $shortEventClassName = $this->eventClassNaming->getShortEventClassName($methodCall);

        /** @var string $className */
        $className = $methodCall->getAttribute(AttributeKey::CLASS_NAME);
        $namespaceAbove = Strings::before($className, '\\', -1);

        return $namespaceAbove . '\\Event\\' . $shortEventClassName;
    }

    private function removeMagicProperty(MethodCall $methodCall): void
    {
        /** @var string $methodName */
        $methodName = $this->getName($methodCall->name);

        /** @var Class_ $class */
        $class = $methodCall->getAttribute(AttributeKey::CLASS_NODE);

        /** @var Property $property */
        $property = $class->getProperty($methodName);

        $this->removeNode($property);
    }

    private function createEventInstanceAssign(string $eventClassName, MethodCall $methodCall): Assign
    {
        $shortEventClassName = $this->classNaming->getVariableName($eventClassName);

        $new = new New_(new FullyQualified($eventClassName));
        if ($methodCall->args) {
            $new->args = $methodCall->args;
        }

        return new Assign(new Variable($shortEventClassName), $new);
    }

    private function createDispatchMethodCall(string $eventClassName): MethodCall
    {
        $shortEventClassName = $this->classNaming->getVariableName($eventClassName);

        $eventDispatcherPropertyFetch = new PropertyFetch(new Variable('this'), 'eventDispatcher');
        $dispatchMethodCall = new MethodCall($eventDispatcherPropertyFetch, 'dispatch');
        $dispatchMethodCall->args[] = new Arg(new Variable($shortEventClassName));

        return $dispatchMethodCall;
    }
}
