<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\Nette\Kdyby\Naming\EventClassNaming;
use Rector\Nette\Kdyby\NodeFactory\EventValueObjectClassFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Kdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector\ReplaceEventManagerWithEventSubscriberRectorTest
 */
final class ReplaceEventManagerWithEventSubscriberRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Nette\Kdyby\Naming\EventClassNaming
     */
    private $eventClassNaming;
    /**
     * @readonly
     * @var \Rector\Nette\Kdyby\NodeFactory\EventValueObjectClassFactory
     */
    private $eventValueObjectClassFactory;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(EventClassNaming $eventClassNaming, EventValueObjectClassFactory $eventValueObjectClassFactory, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->eventClassNaming = $eventClassNaming;
        $this->eventValueObjectClassFactory = $eventValueObjectClassFactory;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change Kdyby EventManager to EventDispatcher', [new CodeSample(<<<'CODE_SAMPLE'
use Kdyby\Events\EventManager;

final class SomeClass
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = eventManager;
    }

    public function run()
    {
        $key = '2000';
        $this->eventManager->dispatchEvent(static::class . '::onCopy', new EventArgsList([$this, $key]));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Kdyby\Events\EventManager;

final class SomeClass
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = eventManager;
    }

    public function run()
    {
        $key = '2000';
        $this->eventManager->dispatch(new SomeClassCopyEvent($this, $key));
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $node->name = new Identifier('dispatch');
        $oldArgs = $node->args;
        $node->args = [];
        $eventReference = $oldArgs[0]->value;
        $classAndStaticProperty = $this->valueResolver->getValue($eventReference, \true);
        if (!\is_string($classAndStaticProperty)) {
            return null;
        }
        $eventClassName = $this->eventClassNaming->createEventClassNameFromClassPropertyReference($classAndStaticProperty);
        $args = $this->createNewArgs($oldArgs);
        $new = new New_(new FullyQualified($eventClassName), $args);
        $node->args[] = new Arg($new);
        // 3. create new event class with args
        $eventClassInNamespace = $this->eventValueObjectClassFactory->create($eventClassName, $args);
        $fileInfo = $this->file->getSmartFileInfo();
        $eventFileLocation = $this->eventClassNaming->resolveEventFileLocationFromClassNameAndFileInfo($eventClassName, $fileInfo);
        $addedFileWithNodes = new AddedFileWithNodes($eventFileLocation, [$eventClassInNamespace]);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
        return $node;
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if (!$this->isObjectType($methodCall->var, new ObjectType('Kdyby\\Events\\EventManager'))) {
            return \true;
        }
        return !$this->isName($methodCall->name, 'dispatchEvent');
    }
    /**
     * @param Arg[] $oldArgs
     * @return Arg[]
     */
    private function createNewArgs(array $oldArgs) : array
    {
        $args = [];
        if ($oldArgs[1]->value instanceof New_) {
            /** @var New_ $new */
            $new = $oldArgs[1]->value;
            $array = $new->args[0]->value;
            if (!$array instanceof Array_) {
                return [];
            }
            foreach ($array->items as $arrayItem) {
                if (!$arrayItem instanceof ArrayItem) {
                    continue;
                }
                $args[] = new Arg($arrayItem->value);
            }
        }
        return $args;
    }
}
