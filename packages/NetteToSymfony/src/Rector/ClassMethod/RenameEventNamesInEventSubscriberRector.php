<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\ClassMethod;

use Composer\Script\Event;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeContainer\ParsedNodesByType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NetteToSymfony\Event\EventInfosFactory;
use Rector\NetteToSymfony\ValueObject\EventInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/contributte/event-dispatcher-extra/blob/master/.docs/README.md#bridge-wrench
 * @see https://symfony.com/doc/current/reference/events.html
 * @see https://symfony.com/doc/current/components/http_kernel.html#creating-an-event-listener
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpKernel/KernelEvents.php
 * @see \Rector\NetteToSymfony\Tests\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector\RenameEventNamesInEventSubscriberRectorTest
 */
final class RenameEventNamesInEventSubscriberRector extends AbstractRector
{
    /**
     * @var string
     */
    private const EVENT_SUBSCRIBER_INTERFACE = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';

    /**
     * @var EventInfo[]
     */
    private $symfonyClassConstWithAliases = [];

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(EventInfosFactory $eventInfosFactory, ParsedNodesByType $parsedNodesByType)
    {
        $this->symfonyClassConstWithAliases = $eventInfosFactory->create();
        $this->parsedNodesByType = $parsedNodesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes event names from Nette ones to Symfony ones', [
            new CodeSample(
                <<<'PHP'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['nette.application' => 'someMethod'];
    }
}
PHP
                ,
                <<<'PHP'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [\SymfonyEvents::KERNEL => 'someMethod'];
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return null;
        }

        if (! $this->isObjectType($class, self::EVENT_SUBSCRIBER_INTERFACE)) {
            return null;
        }

        if (! $this->isName($node, 'getSubscribedEvents')) {
            return null;
        }

        /** @var Return_[] $returnNodes */
        $returnNodes = $this->betterNodeFinder->findInstanceOf($node, Return_::class);

        foreach ($returnNodes as $returnNode) {
            if (! $returnNode->expr instanceof Array_) {
                continue;
            }

            $this->renameArrayKeys($returnNode);
        }

        return $node;
    }

    private function renameArrayKeys(Return_ $returnNode): void
    {
        if (! $returnNode->expr instanceof Array_) {
            return;
        }

        foreach ($returnNode->expr->items as $arrayItem) {
            $eventInfo = $this->matchStringKeys($arrayItem);
            if ($eventInfo === null) {
                $eventInfo = $this->matchClassConstKeys($arrayItem);
            }

            if ($eventInfo === null) {
                continue;
            }

            $arrayItem->key = new ClassConstFetch(new FullyQualified(
                $eventInfo->getClass()
            ), $eventInfo->getConstant());

            // method name
            $className = (string) $returnNode->getAttribute(AttributeKey::CLASS_NAME);
            $methodName = (string) $this->getValue($arrayItem->value);
            $this->processMethodArgument($className, $methodName, $eventInfo);
        }
    }

    private function matchStringKeys(ArrayItem $arrayItem): ?EventInfo
    {
        if (! $arrayItem->key instanceof String_) {
            return null;
        }

        foreach ($this->symfonyClassConstWithAliases as $symfonyClassConst) {
            foreach ($symfonyClassConst->getOldStringAliases() as $netteStringName) {
                if ($this->isValue($arrayItem->key, $netteStringName)) {
                    return $symfonyClassConst;
                }
            }
        }

        return null;
    }

    private function matchClassConstKeys(ArrayItem $arrayItem): ?EventInfo
    {
        if (! $arrayItem->key instanceof ClassConstFetch) {
            return null;
        }

        foreach ($this->symfonyClassConstWithAliases as $symfonyClassConst) {
            $isMatch = $this->resolveClassConstAliasMatch($arrayItem, $symfonyClassConst);
            if ($isMatch) {
                return $symfonyClassConst;
            }
        }

        return null;
    }

    private function processMethodArgument(string $class, string $method, EventInfo $eventInfo): void
    {
        $classMethodNode = $this->parsedNodesByType->findMethod($method, $class);
        if ($classMethodNode === null) {
            return;
        }

        if (count((array) $classMethodNode->params) !== 1) {
            return;
        }

        $classMethodNode->params[0]->type = new FullyQualified($eventInfo->getEventClass());
    }

    private function resolveClassConstAliasMatch(ArrayItem $arrayItem, EventInfo $eventInfo): bool
    {
        foreach ($eventInfo->getOldClassConstAlaises() as $netteClassConst) {
            $classConstFetchNode = $arrayItem->key;
            if ($classConstFetchNode === null) {
                continue;
            }

            if ($this->isName($classConstFetchNode, $netteClassConst)) {
                return true;
            }
        }

        return false;
    }
}
