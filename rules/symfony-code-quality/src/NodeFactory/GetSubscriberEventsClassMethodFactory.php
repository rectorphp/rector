<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\Manipulator\VisibilityManipulator;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Symfony\Contract\Tag\TagInterface;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\Tag;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;
use Rector\SymfonyCodeQuality\ValueObject\EventNameToClassAndConstant;

final class GetSubscriberEventsClassMethodFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var VisibilityManipulator
     */
    private $visibilityManipulator;

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        VisibilityManipulator $visibilityManipulator,
    PhpVersionProvider $phpVersionProvider,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    /**
     * @param array<string, ServiceDefinition[]> $eventsToMethods
     * @param EventNameToClassAndConstant[] $eventNamesToClassConstants
     */
    public function createFromEventsToMethods(array $eventsToMethods, array $eventNamesToClassConstants): ClassMethod
    {
        $getSubscribersClassMethod = $this->nodeFactory->createPublicMethod('getSubscribedEvents');

        $eventsToMethodsArray = new Array_();

        $this->visibilityManipulator->makeStatic($getSubscribersClassMethod);

        foreach ($eventsToMethods as $eventName => $methodNamesWithPriorities) {
            $eventNameExpr = $this->createEventName($eventName, $eventNamesToClassConstants);

            if (count($methodNamesWithPriorities) === 1) {
                $this->createSingleMethod(
                    $methodNamesWithPriorities,
                    $eventName,
                    $eventNameExpr,
                    $eventsToMethodsArray
                );
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
     * @param ClassConstFetch|String_ $expr
     * @param ServiceDefinition[] $methodNamesWithPriorities
     */
    private function createSingleMethod(
        array $methodNamesWithPriorities,
        string $eventName,
        Expr $expr,
        Array_ $eventsToMethodsArray
    ): void {

        /** @var EventListenerTag[]|Tag[] $eventTags */
        $eventTags = $methodNamesWithPriorities[0]->getTags();
        foreach ($eventTags as $eventTag) {
            if ($eventTag instanceof EventListenerTag && $eventTag->getEvent() === $eventName) {
                $methodName = $eventTag->getMethod();
                $priority = $eventTag->getPriority();
                break;
            }
        }

        if (! isset($methodName, $priority)) {
            return;
        }

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
                if (! $tag instanceof EventListenerTag) {
                    continue;
                }

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
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new Identifier('array');
        }

        $returnType = new ArrayType(new MixedType(), new MixedType(true));
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->changeReturnType($returnType);
    }

    /**
     * @param TagInterface[] $alreadyUsedTags
     */
    private function shouldSkip(string $eventName, EventListenerTag $eventListenerTag, array $alreadyUsedTags): bool
    {
        if ($eventName !== $eventListenerTag->getEvent()) {
            return true;
        }

        return in_array($eventListenerTag, $alreadyUsedTags, true);
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

    /**
     * @param EventNameToClassAndConstant[] $eventNamesToClassConstants
     * @return String_|ClassConstFetch
     */
    private function createEventName(string $eventName, array $eventNamesToClassConstants): Node
    {
        if (class_exists($eventName)) {
            return $this->nodeFactory->createClassConstReference($eventName);
        }

        // is string a that could be caught in constant, e.g. KernelEvents?
        foreach ($eventNamesToClassConstants as $eventNameToClassConstant) {
            if ($eventNameToClassConstant->getEventName() !== $eventName) {
                continue;
            }

            return $this->nodeFactory->createClassConstFetch(
                $eventNameToClassConstant->getEventClass(),
                $eventNameToClassConstant->getEventConstant()
            );
        }

        return new String_($eventName);
    }
}
