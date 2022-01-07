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
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Symfony\Contract\EventReferenceToMethodNameInterface;
use Rector\Symfony\Contract\Tag\TagInterface;
use Rector\Symfony\ValueObject\EventNameToClassAndConstant;
use Rector\Symfony\ValueObject\EventReferenceToMethodNameWithPriority;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\Tag;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;
final class GetSubscribedEventsClassMethodFactory
{
    /**
     * @var string
     */
    private const GET_SUBSCRIBED_EVENTS_METHOD_NAME = 'getSubscribedEvents';
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
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
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Symfony\NodeFactory\EventReferenceFactory $eventReferenceFactory)
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
    public function create(array $eventReferencesToMethodNames) : \PhpParser\Node\Stmt\ClassMethod
    {
        $getSubscribersClassMethod = $this->createClassMethod();
        $eventsToMethodsArray = new \PhpParser\Node\Expr\Array_();
        foreach ($eventReferencesToMethodNames as $eventReferenceToMethodName) {
            $priority = $eventReferenceToMethodName instanceof \Rector\Symfony\ValueObject\EventReferenceToMethodNameWithPriority ? $eventReferenceToMethodName->getPriority() : null;
            $eventsToMethodsArray->items[] = $this->createArrayItemFromMethodAndPriority($priority, $eventReferenceToMethodName->getMethodName(), $eventReferenceToMethodName->getClassConstFetch());
        }
        $getSubscribersClassMethod->stmts[] = new \PhpParser\Node\Stmt\Return_($eventsToMethodsArray);
        $this->decorateClassMethodWithReturnType($getSubscribersClassMethod);
        return $getSubscribersClassMethod;
    }
    /**
     * @param array<string, ServiceDefinition[]> $eventsToMethods
     * @param EventNameToClassAndConstant[] $eventNamesToClassConstants
     */
    public function createFromServiceDefinitionsAndEventsToMethods(array $eventsToMethods, array $eventNamesToClassConstants) : \PhpParser\Node\Stmt\ClassMethod
    {
        $getSubscribersClassMethod = $this->createClassMethod();
        $eventsToMethodsArray = new \PhpParser\Node\Expr\Array_();
        foreach ($eventsToMethods as $eventName => $methodNamesWithPriorities) {
            $eventNameExpr = $this->eventReferenceFactory->createEventName($eventName, $eventNamesToClassConstants);
            // just method name, without a priority
            //            if (! is_array($methodNamesWithPriorities)) {
            //                $methodNamesWithPriorities = [$methodNamesWithPriorities];
            //            }
            if (\count($methodNamesWithPriorities) === 1) {
                $this->createSingleMethod($methodNamesWithPriorities, $eventName, $eventNameExpr, $eventsToMethodsArray);
            } else {
                $this->createMultipleMethods($methodNamesWithPriorities, $eventNameExpr, $eventsToMethodsArray, $eventName);
            }
        }
        $getSubscribersClassMethod->stmts[] = new \PhpParser\Node\Stmt\Return_($eventsToMethodsArray);
        $this->decorateClassMethodWithReturnType($getSubscribersClassMethod);
        return $getSubscribersClassMethod;
    }
    private function createClassMethod() : \PhpParser\Node\Stmt\ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod(self::GET_SUBSCRIBED_EVENTS_METHOD_NAME);
        $this->visibilityManipulator->makeStatic($classMethod);
        return $classMethod;
    }
    private function createArrayItemFromMethodAndPriority(?int $priority, string $methodName, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr\ArrayItem
    {
        if ($priority !== null && $priority !== 0) {
            $methodNameWithPriorityArray = new \PhpParser\Node\Expr\Array_();
            $methodNameWithPriorityArray->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_($methodName));
            $methodNameWithPriorityArray->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\LNumber($priority));
            return new \PhpParser\Node\Expr\ArrayItem($methodNameWithPriorityArray, $expr);
        }
        return new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_($methodName), $expr);
    }
    private function decorateClassMethodWithReturnType(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new \PhpParser\Node\Identifier('array');
        }
        $returnType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\StringType(), new \PHPStan\Type\MixedType(\true));
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $returnType);
    }
    /**
     * @param ServiceDefinition[] $methodNamesWithPriorities
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Scalar\String_ $expr
     */
    private function createSingleMethod(array $methodNamesWithPriorities, string $eventName, $expr, \PhpParser\Node\Expr\Array_ $eventsToMethodsArray) : void
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
    private function createMultipleMethods(array $methodNamesWithPriorities, $expr, \PhpParser\Node\Expr\Array_ $eventsToMethodsArray, string $eventName) : void
    {
        $eventItems = [];
        $alreadyUsedTags = [];
        foreach ($methodNamesWithPriorities as $methodNameWithPriority) {
            foreach ($methodNameWithPriority->getTags() as $tag) {
                if (!$tag instanceof \Rector\Symfony\ValueObject\Tag\EventListenerTag) {
                    continue;
                }
                if ($this->shouldSkip($eventName, $tag, $alreadyUsedTags)) {
                    continue;
                }
                $eventItems[] = $this->createEventItem($tag);
                $alreadyUsedTags[] = $tag;
            }
        }
        $multipleMethodsArray = new \PhpParser\Node\Expr\Array_($eventItems);
        $eventsToMethodsArray->items[] = new \PhpParser\Node\Expr\ArrayItem($multipleMethodsArray, $expr);
    }
    private function resolveMethodName(\Rector\Symfony\ValueObject\ServiceDefinition $serviceDefinition, string $eventName) : ?string
    {
        /** @var EventListenerTag[]|Tag[] $eventTags */
        $eventTags = $serviceDefinition->getTags();
        foreach ($eventTags as $eventTag) {
            if (!$eventTag instanceof \Rector\Symfony\ValueObject\Tag\EventListenerTag) {
                continue;
            }
            if ($eventTag->getEvent() !== $eventName) {
                continue;
            }
            return $eventTag->getMethod();
        }
        return null;
    }
    private function resolvePriority(\Rector\Symfony\ValueObject\ServiceDefinition $serviceDefinition, string $eventName) : ?int
    {
        /** @var EventListenerTag[]|Tag[] $eventTags */
        $eventTags = $serviceDefinition->getTags();
        foreach ($eventTags as $eventTag) {
            if (!$eventTag instanceof \Rector\Symfony\ValueObject\Tag\EventListenerTag) {
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
    private function shouldSkip(string $eventName, \Rector\Symfony\ValueObject\Tag\EventListenerTag $eventListenerTag, array $alreadyUsedTags) : bool
    {
        if ($eventName !== $eventListenerTag->getEvent()) {
            return \true;
        }
        return \in_array($eventListenerTag, $alreadyUsedTags, \true);
    }
    private function createEventItem(\Rector\Symfony\ValueObject\Tag\EventListenerTag $eventListenerTag) : \PhpParser\Node\Expr\ArrayItem
    {
        if ($eventListenerTag->getPriority() !== 0) {
            $methodNameWithPriorityArray = new \PhpParser\Node\Expr\Array_();
            $methodNameWithPriorityArray->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_($eventListenerTag->getMethod()));
            $methodNameWithPriorityArray->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\LNumber($eventListenerTag->getPriority()));
            return new \PhpParser\Node\Expr\ArrayItem($methodNameWithPriorityArray);
        }
        return new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_($eventListenerTag->getMethod()));
    }
}
