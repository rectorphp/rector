<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\NodeAnalyzer\ClassAnalyzer;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Class_\EventSubscriberToAttributeRector\EventSubscriberToAttributeRectorTest
 */
final class EventSubscriberToAttributeRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ClassAnalyzer $classAnalyzer;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        // #[AsEventListener] attribute allowed on methods since Symfony 5.4
        return new ComposerPackageConstraint('symfony/event-dispatcher', '>=5.4');
    }
    public function __construct(ClassAnalyzer $classAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change Symfony EventSubscriberInterface implementation to #[AsEventListener] attributes', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['some_event' => ['methodToBeCalled', 10]];
    }

    public function methodToBeCalled()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class SomeEventSubscriber
{
    #[AsEventListener(event: 'some_event', priority: 10)]
    public function methodToBeCalled()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->classAnalyzer->hasImplements($node, SymfonyClass::EVENT_SUBSCRIBER_INTERFACE)) {
            return null;
        }
        $getSubscribedEventsClassMethod = $node->getMethod('getSubscribedEvents');
        if (!$getSubscribedEventsClassMethod instanceof ClassMethod) {
            return null;
        }
        $eventsArray = $this->getReturnArray($getSubscribedEventsClassMethod);
        if (!$eventsArray instanceof Array_) {
            return null;
        }
        $listeners = $this->resolveListeners($node, $eventsArray);
        if ($listeners === null) {
            return null;
        }
        foreach ($listeners as [$classMethod, $attributeGroup]) {
            $classMethod->attrGroups[] = $attributeGroup;
        }
        $node->stmts = array_values(array_filter($node->stmts, static fn(Stmt $stmt): bool => $stmt !== $getSubscribedEventsClassMethod));
        $node->implements = array_values(array_filter($node->implements, fn(Node $name): bool => !$this->isName($name, SymfonyClass::EVENT_SUBSCRIBER_INTERFACE)));
        return $node;
    }
    private function isFormEvent(Expr $eventExpr): bool
    {
        if ($eventExpr instanceof ClassConstFetch) {
            if ($this->isName($eventExpr->class, SymfonyClass::FORM_EVENTS)) {
                return \true;
            }
            // event classes keyed via FormEvents::ALIASES, e.g. PreSubmitEvent::class
            $className = $this->getName($eventExpr->class);
            return $className !== null && strncmp($className, 'Symfony\Component\Form\Event\\', strlen('Symfony\Component\Form\Event\\')) === 0;
        }
        // FormEvents::* constants all resolve to "form.*" string values
        return $eventExpr instanceof String_ && strncmp($eventExpr->value, 'form.', strlen('form.')) === 0;
    }
    private function getReturnArray(ClassMethod $classMethod): ?Array_
    {
        $stmts = (array) $classMethod->stmts;
        if (count($stmts) !== 1) {
            return null;
        }
        $return = $stmts[0];
        if (!$return instanceof Return_) {
            return null;
        }
        return $return->expr instanceof Array_ ? $return->expr : null;
    }
    /**
     * @return array<array{ClassMethod, AttributeGroup}>|null
     */
    private function resolveListeners(Class_ $class, Array_ $eventsArray): ?array
    {
        $listeners = [];
        foreach ($eventsArray->items as $item) {
            if ($item->key === null) {
                return null;
            }
            // form events are usually registered manually, keep them as is
            if ($this->isFormEvent($item->key)) {
                return null;
            }
            $methodsWithPriorities = $this->resolveMethodsWithPriorities($item->value);
            if ($methodsWithPriorities === null) {
                return null;
            }
            foreach ($methodsWithPriorities as [$methodName, $priorityExpr]) {
                $classMethod = $class->getMethod($methodName);
                if (!$classMethod instanceof ClassMethod) {
                    return null;
                }
                if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, SymfonyAttribute::AS_EVENT_LISTENER)) {
                    return null;
                }
                $listeners[] = [$classMethod, $this->createAttributeGroup($item->key, $priorityExpr)];
            }
        }
        return $listeners;
    }
    /**
     * @return array<array{string, Expr|null}>|null
     */
    private function resolveMethodsWithPriorities(Expr $expr): ?array
    {
        if ($expr instanceof String_) {
            return [[$expr->value, null]];
        }
        if (!$expr instanceof Array_ || $expr->items === []) {
            return null;
        }
        $listenerArrays = [$expr];
        if (!$expr->items[0]->value instanceof String_) {
            $listenerArrays = array_map(static fn(ArrayItem $item): Expr => $item->value, $expr->items);
        }
        $methodsWithPriorities = [];
        foreach ($listenerArrays as $listenerArray) {
            if (!$listenerArray instanceof Array_ || count($listenerArray->items) > 2) {
                return null;
            }
            $methodName = $listenerArray->items[0] ?? null;
            $priority = $listenerArray->items[1] ?? null;
            if (!$methodName instanceof ArrayItem || !$methodName->value instanceof String_ || $methodName->key instanceof Expr) {
                return null;
            }
            if ($priority instanceof ArrayItem && ($priority->key instanceof Expr || $priority->value instanceof String_)) {
                return null;
            }
            // priority 0 is the default, drop it to keep the attribute clean
            $priorityExpr = ($nullsafeVariable1 = $priority) ? $nullsafeVariable1->value : null;
            if ($priorityExpr instanceof Int_ && $priorityExpr->value === 0) {
                $priorityExpr = null;
            }
            $methodsWithPriorities[] = [$methodName->value->value, $priorityExpr];
        }
        return $methodsWithPriorities;
    }
    private function createAttributeGroup(Expr $eventExpr, ?Expr $priorityExpr): AttributeGroup
    {
        $args = [new Arg($eventExpr, \false, \false, [], new Identifier('event'))];
        if ($priorityExpr instanceof Expr) {
            $args[] = new Arg($priorityExpr, \false, \false, [], new Identifier('priority'));
        }
        return new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::AS_EVENT_LISTENER), $args)]);
    }
}
