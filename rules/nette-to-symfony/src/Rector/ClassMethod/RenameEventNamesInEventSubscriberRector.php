<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\ClassMethod;

use Composer\Script\Event;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NetteToSymfony\Event\EventInfosFactory;
use Rector\NetteToSymfony\ValueObject\EventInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/contributte/event-dispatcher-extra/blob/master/.docs/README.md#bridge-wrench
 * @see https://symfony.com/doc/current/reference/events.html
 * @see https://symfony.com/doc/current/components/http_kernel.html#creating-an-event-listener
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpKernel/KernelEvents.php
 *
 * @see \Rector\NetteToSymfony\Tests\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector\RenameEventNamesInEventSubscriberRectorTest
 */
final class RenameEventNamesInEventSubscriberRector extends AbstractRector
{
    /**
     * @var EventInfo[]
     */
    private $symfonyClassConstWithAliases = [];

    public function __construct(EventInfosFactory $eventInfosFactory)
    {
        $this->symfonyClassConstWithAliases = $eventInfosFactory->create();
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes event names from Nette ones to Symfony ones',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['nette.application' => 'someMethod'];
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [\SymfonyEvents::KERNEL => 'someMethod'];
    }
}
CODE_SAMPLE
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
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        if (! $this->isObjectType($classLike, 'Symfony\Component\EventDispatcher\EventSubscriberInterface')) {
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

    private function renameArrayKeys(Return_ $return): void
    {
        if (! $return->expr instanceof Array_) {
            return;
        }

        foreach ($return->expr->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }

            $eventInfo = $this->matchStringKeys($arrayItem);
            if (! $eventInfo instanceof EventInfo) {
                $eventInfo = $this->matchClassConstKeys($arrayItem);
            }

            if (! $eventInfo instanceof EventInfo) {
                continue;
            }

            $arrayItem->key = new ClassConstFetch(new FullyQualified(
                $eventInfo->getClass()
            ), $eventInfo->getConstant());

            // method name
            $className = (string) $return->getAttribute(AttributeKey::CLASS_NAME);
            $methodName = (string) $this->valueResolver->getValue($arrayItem->value);
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
                if ($this->valueResolver->isValue($arrayItem->key, $netteStringName)) {
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
        $classMethodNode = $this->nodeRepository->findClassMethod($class, $method);
        if (! $classMethodNode instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }

        if (count($classMethodNode->params) !== 1) {
            return;
        }

        $classMethodNode->params[0]->type = new FullyQualified($eventInfo->getEventClass());
    }

    private function resolveClassConstAliasMatch(ArrayItem $arrayItem, EventInfo $eventInfo): bool
    {
        $classConstFetchNode = $arrayItem->key;
        if (! $classConstFetchNode instanceof Expr) {
            return false;
        }

        foreach ($eventInfo->getOldClassConstAliases() as $netteClassConst) {
            if ($this->isName($classConstFetchNode, $netteClassConst)) {
                return true;
            }
        }

        return false;
    }
}
