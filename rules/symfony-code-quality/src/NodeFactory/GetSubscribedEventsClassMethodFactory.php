<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\NodeFactory;

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
use PHPStan\Type\StringType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Symfony\Contract\Tag\TagInterface;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\Tag;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;
use Rector\SymfonyCodeQuality\Contract\EventReferenceToMethodNameInterface;
use Rector\SymfonyCodeQuality\ValueObject\EventNameToClassAndConstant;
use Rector\SymfonyCodeQuality\ValueObject\EventReferenceToMethodNameWithPriority;

final class GetSubscribedEventsClassMethodFactory
{
    /**
     * @var string
     */
    private const GET_SUBSCRIBED_EVENTS_METHOD_NAME = 'getSubscribedEvents';

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

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @var EventReferenceFactory
     */
    private $eventReferenceFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        VisibilityManipulator $visibilityManipulator,
        PhpVersionProvider $phpVersionProvider,
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocTypeChanger $phpDocTypeChanger,
        EventReferenceFactory $eventReferenceFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->eventReferenceFactory = $eventReferenceFactory;
    }

    /**
     * @param EventReferenceToMethodNameInterface[] $eventReferencesToMethodNames
     */
    public function create(array $eventReferencesToMethodNames): ClassMethod
    {
        $getSubscribersClassMethod = $this->createClassMethod();

        $eventsToMethodsArray = new Array_();

        foreach ($eventReferencesToMethodNames as $eventReferencesToMethodName) {
            $priority = $eventReferencesToMethodName instanceof EventReferenceToMethodNameWithPriority ? $eventReferencesToMethodName->getPriority() : null;

            $eventsToMethodsArray->items[] = $this->createArrayItemFromMethodAndPriority(
                $priority,
                $eventReferencesToMethodName->getMethodName(),
                $eventReferencesToMethodName->getClassConstFetch()
            );
        }

        $getSubscribersClassMethod->stmts[] = new Return_($eventsToMethodsArray);

        $this->decorateClassMethodWithReturnType($getSubscribersClassMethod);

        return $getSubscribersClassMethod;
    }

    /**
     * @param array<string, ServiceDefinition[]> $eventsToMethods
     * @param EventNameToClassAndConstant[] $eventNamesToClassConstants
     */
    public function createFromServiceDefinitionsAndEventsToMethods(
        array $eventsToMethods,
        array $eventNamesToClassConstants
    ): ClassMethod {
        $getSubscribersClassMethod = $this->createClassMethod();

        $eventsToMethodsArray = new Array_();

        foreach ($eventsToMethods as $eventName => $methodNamesWithPriorities) {
            $eventNameExpr = $this->eventReferenceFactory->createEventName($eventName, $eventNamesToClassConstants);

            // just method name, without a priority
            if (! is_array($methodNamesWithPriorities)) {
                $methodNamesWithPriorities = [$methodNamesWithPriorities];
            }

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

    private function createClassMethod(): ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod(self::GET_SUBSCRIBED_EVENTS_METHOD_NAME);
        $this->visibilityManipulator->makeStatic($classMethod);

        return $classMethod;
    }

    private function createArrayItemFromMethodAndPriority(?int $priority, string $methodName, Expr $expr): ArrayItem
    {
        if ($priority !== null && $priority !== 0) {
            $methodNameWithPriorityArray = new Array_();
            $methodNameWithPriorityArray->items[] = new ArrayItem(new String_($methodName));
            $methodNameWithPriorityArray->items[] = new ArrayItem(new LNumber((int) $priority));

            return new ArrayItem($methodNameWithPriorityArray, $expr);
        }

        return new ArrayItem(new String_($methodName), $expr);
    }

    private function decorateClassMethodWithReturnType(ClassMethod $classMethod): void
    {
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new Identifier('array');
        }

        $returnType = new ArrayType(new StringType(), new MixedType(true));
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $returnType);
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
        $methodName = $this->resolveMethodName($methodNamesWithPriorities[0], $eventName);
        $priority = $this->resolvePriority($methodNamesWithPriorities[0], $eventName);
        if ($methodName === null) {
            return;
        }

        $eventsToMethodsArray->items[] = $this->createArrayItemFromMethodAndPriority($priority, $methodName, $expr);
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

    private function resolveMethodName(ServiceDefinition $serviceDefinition, string $eventName): ?string
    {
        /** @var EventListenerTag[]|Tag[] $eventTags */
        $eventTags = $serviceDefinition->getTags();
        foreach ($eventTags as $eventTag) {
            if ($eventTag instanceof EventListenerTag && $eventTag->getEvent() === $eventName) {
                return $eventTag->getMethod();
            }
        }

        return null;
    }

    private function resolvePriority(ServiceDefinition $serviceDefinition, string $eventName): ?int
    {
        /** @var EventListenerTag[]|Tag[] $eventTags */
        $eventTags = $serviceDefinition->getTags();
        foreach ($eventTags as $eventTag) {
            if ($eventTag instanceof EventListenerTag && $eventTag->getEvent() === $eventName) {
                return $eventTag->getPriority();
            }
        }

        return null;
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
}
