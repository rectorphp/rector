<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

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
use Rector\Php\PhpVersionProvider;
use Rector\PhpParser\Node\NodeFactory;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Symfony\Contract\EventReferenceToMethodNameInterface;
use Rector\Symfony\Contract\Tag\TagInterface;
use Rector\Symfony\ValueObject\EventNameToClassAndConstant;
use Rector\Symfony\ValueObject\EventReferenceToMethodNameWithPriority;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\Tag;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;
use Rector\ValueObject\PhpVersionFeature;
final class GetSubscribedEventsClassMethodFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\EventReferenceFactory
     */
    private $eventReferenceFactory;
    /**
     * @var string
     */
    private const GET_SUBSCRIBED_EVENTS_METHOD_NAME = 'getSubscribedEvents';
    public function __construct(NodeFactory $nodeFactory, VisibilityManipulator $visibilityManipulator, PhpVersionProvider $phpVersionProvider, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, \Rector\Symfony\NodeFactory\EventReferenceFactory $eventReferenceFactory)
    {
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
    public function create(array $eventReferencesToMethodNames) : ClassMethod
    {
        $getSubscribersClassMethod = $this->createClassMethod();
        $eventsToMethodsArray = new Array_();
        foreach ($eventReferencesToMethodNames as $eventReferenceToMethodName) {
            $priority = $eventReferenceToMethodName instanceof EventReferenceToMethodNameWithPriority ? $eventReferenceToMethodName->getPriority() : null;
            $eventsToMethodsArray->items[] = $this->createArrayItemFromMethodAndPriority($priority, $eventReferenceToMethodName->getMethodName(), $eventReferenceToMethodName->getClassConstFetch());
        }
        $getSubscribersClassMethod->stmts[] = new Return_($eventsToMethodsArray);
        $this->decorateClassMethodWithReturnType($getSubscribersClassMethod);
        return $getSubscribersClassMethod;
    }
    /**
     * @param array<string, ServiceDefinition[]> $eventsToMethods
     * @param EventNameToClassAndConstant[] $eventNamesToClassConstants
     */
    public function createFromServiceDefinitionsAndEventsToMethods(array $eventsToMethods, array $eventNamesToClassConstants) : ClassMethod
    {
        $getSubscribersClassMethod = $this->createClassMethod();
        $eventsToMethodsArray = new Array_();
        foreach ($eventsToMethods as $eventName => $methodNamesWithPriorities) {
            $eventNameExpr = $this->eventReferenceFactory->createEventName($eventName, $eventNamesToClassConstants);
            if (\count($methodNamesWithPriorities) === 1) {
                $this->createSingleMethod($methodNamesWithPriorities, $eventName, $eventNameExpr, $eventsToMethodsArray);
            } else {
                $this->createMultipleMethods($methodNamesWithPriorities, $eventNameExpr, $eventsToMethodsArray, $eventName);
            }
        }
        $getSubscribersClassMethod->stmts[] = new Return_($eventsToMethodsArray);
        $this->decorateClassMethodWithReturnType($getSubscribersClassMethod);
        return $getSubscribersClassMethod;
    }
    private function createClassMethod() : ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod(self::GET_SUBSCRIBED_EVENTS_METHOD_NAME);
        $this->visibilityManipulator->makeStatic($classMethod);
        return $classMethod;
    }
    private function createArrayItemFromMethodAndPriority(?int $priority, string $methodName, Expr $expr) : ArrayItem
    {
        if ($priority !== null && $priority !== 0) {
            $methodNameWithPriorityArray = new Array_();
            $methodNameWithPriorityArray->items[] = new ArrayItem(new String_($methodName));
            $methodNameWithPriorityArray->items[] = new ArrayItem(new LNumber($priority));
            return new ArrayItem($methodNameWithPriorityArray, $expr);
        }
        return new ArrayItem(new String_($methodName), $expr);
    }
    private function decorateClassMethodWithReturnType(ClassMethod $classMethod) : void
    {
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new Identifier('array');
        }
        $returnType = new ArrayType(new StringType(), new MixedType(\true));
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeReturnType($classMethod, $phpDocInfo, $returnType);
    }
    /**
     * @param ServiceDefinition[] $methodNamesWithPriorities
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Scalar\String_ $expr
     */
    private function createSingleMethod(array $methodNamesWithPriorities, string $eventName, $expr, Array_ $eventsToMethodsArray) : void
    {
        $methodName = $this->resolveMethodName($methodNamesWithPriorities[0], $eventName);
        $priority = $this->resolvePriority($methodNamesWithPriorities[0], $eventName);
        if ($methodName === null) {
            return;
        }
        $eventsToMethodsArray->items[] = $this->createArrayItemFromMethodAndPriority($priority, $methodName, $expr);
    }
    /**
     * @param ServiceDefinition[] $methodNamesWithPriorities
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Scalar\String_ $expr
     */
    private function createMultipleMethods(array $methodNamesWithPriorities, $expr, Array_ $eventsToMethodsArray, string $eventName) : void
    {
        $eventItems = [];
        $alreadyUsedTags = [];
        foreach ($methodNamesWithPriorities as $methodNameWithPriority) {
            foreach ($methodNameWithPriority->getTags() as $tag) {
                if (!$tag instanceof EventListenerTag) {
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
    private function resolveMethodName(ServiceDefinition $serviceDefinition, string $eventName) : ?string
    {
        /** @var EventListenerTag[]|Tag[] $eventTags */
        $eventTags = $serviceDefinition->getTags();
        foreach ($eventTags as $eventTag) {
            if (!$eventTag instanceof EventListenerTag) {
                continue;
            }
            if ($eventTag->getEvent() !== $eventName) {
                continue;
            }
            return $eventTag->getMethod();
        }
        return null;
    }
    private function resolvePriority(ServiceDefinition $serviceDefinition, string $eventName) : ?int
    {
        /** @var EventListenerTag[]|Tag[] $eventTags */
        $eventTags = $serviceDefinition->getTags();
        foreach ($eventTags as $eventTag) {
            if (!$eventTag instanceof EventListenerTag) {
                continue;
            }
            if ($eventTag->getEvent() !== $eventName) {
                continue;
            }
            return $eventTag->getPriority();
        }
        return null;
    }
    /**
     * @param TagInterface[] $alreadyUsedTags
     */
    private function shouldSkip(string $eventName, EventListenerTag $eventListenerTag, array $alreadyUsedTags) : bool
    {
        if ($eventName !== $eventListenerTag->getEvent()) {
            return \true;
        }
        return \in_array($eventListenerTag, $alreadyUsedTags, \true);
    }
    private function createEventItem(EventListenerTag $eventListenerTag) : ArrayItem
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
