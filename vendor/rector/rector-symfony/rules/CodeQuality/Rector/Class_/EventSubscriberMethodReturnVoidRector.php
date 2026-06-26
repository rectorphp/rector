<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Class_\EventSubscriberMethodReturnVoidRector\EventSubscriberMethodReturnVoidRectorTest
 */
final class EventSubscriberMethodReturnVoidRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @var string[]
     */
    private const EVENT_CLASSES = ['Symfony\Contracts\EventDispatcher\Event', 'Symfony\Component\EventDispatcher\Event'];
    public function __construct(ReflectionResolver $reflectionResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Event subscriber methods hooked in getSubscribedEvents() must return void, as the event is passed by reference and returning it has no effect', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['some_event' => 'onEvent'];
    }

    public function onEvent(Event $event): Event
    {
        return $event->setSomething('value');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['some_event' => 'onEvent'];
    }

    public function onEvent(Event $event): void
    {
        $event->setSomething('value');
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
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->is(SymfonyClass::EVENT_SUBSCRIBER_INTERFACE)) {
            return null;
        }
        $getSubscribedEventsClassMethod = $node->getMethod('getSubscribedEvents');
        if (!$getSubscribedEventsClassMethod instanceof ClassMethod) {
            return null;
        }
        $hookMethodNames = $this->resolveHookMethodNames($getSubscribedEventsClassMethod);
        if ($hookMethodNames === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($hookMethodNames as $hookMethodName) {
            $classMethod = $node->getMethod($hookMethodName);
            if (!$classMethod instanceof ClassMethod) {
                continue;
            }
            if ($this->refactorHookMethod($classMethod)) {
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function refactorHookMethod(ClassMethod $classMethod): bool
    {
        // void already
        if ($classMethod->returnType instanceof Identifier && $classMethod->returnType->toString() === 'void') {
            return \false;
        }
        if (!$this->hasOnlyEventReturns($classMethod)) {
            return \false;
        }
        $this->rewriteEventReturns($classMethod);
        $classMethod->returnType = new Identifier('void');
        return \true;
    }
    /**
     * @return string[]
     */
    private function resolveHookMethodNames(ClassMethod $getSubscribedEventsClassMethod): array
    {
        $methodNames = [];
        $returns = $this->betterNodeFinder->findInstancesOf((array) $getSubscribedEventsClassMethod->stmts, [Return_::class]);
        foreach ($returns as $return) {
            if (!$return->expr instanceof Array_) {
                continue;
            }
            foreach ($return->expr->items as $arrayItem) {
                foreach ($this->resolveMethodNamesFromValue($arrayItem->value) as $methodName) {
                    $methodNames[] = $methodName;
                }
            }
        }
        return array_unique($methodNames);
    }
    /**
     * Each event maps to: 'method', ['method', priority] or [['method', priority], ['method2']]
     *
     * @return string[]
     */
    private function resolveMethodNamesFromValue(Expr $expr): array
    {
        if ($expr instanceof String_) {
            return [$expr->value];
        }
        if (!$expr instanceof Array_) {
            return [];
        }
        $firstItem = $expr->items[0] ?? null;
        if ($firstItem === null) {
            return [];
        }
        // single listener: ['method', priority]
        if ($firstItem->value instanceof String_) {
            return [$firstItem->value->value];
        }
        // multiple listeners: [['method', priority], ['method2']]
        $methodNames = [];
        foreach ($expr->items as $arrayItem) {
            if (!$arrayItem->value instanceof Array_) {
                continue;
            }
            $nestedFirstItem = $arrayItem->value->items[0] ?? null;
            if ($nestedFirstItem !== null && $nestedFirstItem->value instanceof String_) {
                $methodNames[] = $nestedFirstItem->value->value;
            }
        }
        return $methodNames;
    }
    private function hasOnlyEventReturns(ClassMethod $classMethod): bool
    {
        $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
        $hasEventReturn = \false;
        foreach ($returns as $return) {
            // bare "return;" is fine
            if (!$return->expr instanceof Expr) {
                continue;
            }
            if (!$this->isEventExpr($return->expr)) {
                return \false;
            }
            $hasEventReturn = \true;
        }
        return $hasEventReturn;
    }
    private function isEventExpr(Expr $expr): bool
    {
        $exprType = $this->getType($expr);
        if (!$exprType instanceof ObjectType) {
            return \false;
        }
        $found = \false;
        foreach (self::EVENT_CLASSES as $eventClass) {
            if ($exprType->isInstanceOf($eventClass)->yes()) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    private function rewriteEventReturns(ClassMethod $classMethod): void
    {
        if ($classMethod->stmts === null) {
            return;
        }
        $lastStmt = end($classMethod->stmts) ?: null;
        $this->traverseNodesWithCallable($classMethod, function (Node $node) use ($lastStmt): ?int {
            // do not touch nested function-like returns
            if ($node instanceof Closure || $node instanceof Function_ || $node instanceof ArrowFunction) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!NodeGroup::isStmtAwareNode($node)) {
                return null;
            }
            /** @var StmtsAware $node */
            $this->rewriteStmts($node, $lastStmt);
            return null;
        });
    }
    /**
     * @param StmtsAware $stmtsAware
     */
    private function rewriteStmts(Node $stmtsAware, ?Node $lastStmt): void
    {
        if ($stmtsAware->stmts === null) {
            return;
        }
        $newStmts = [];
        foreach ($stmtsAware->stmts as $stmt) {
            if ($stmt instanceof Return_ && $stmt->expr instanceof Expr && $this->isEventExpr($stmt->expr)) {
                $newStmts[] = new Expression($stmt->expr);
                // keep early return to preserve control flow, unless it is the very last statement
                if ($stmt !== $lastStmt) {
                    $newStmts[] = new Return_();
                }
                continue;
            }
            $newStmts[] = $stmt;
        }
        $stmtsAware->stmts = $newStmts;
    }
}
