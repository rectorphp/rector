<?php

declare (strict_types=1);
namespace Rector\Doctrine\Bundle210\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/DoctrineBundle/pull/1592
 *
 * @see \Rector\Doctrine\Tests\Bundle210\Rector\Class_\EventSubscriberInterfaceToAttributeRector\EventSubscriberInterfaceToAttributeRectorTest
 */
final class EventSubscriberInterfaceToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace EventSubscriberInterface with #[AsDoctrineListener] attribute', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\Common\EventSubscriberInterface;
use Doctrine\ORM\Events;

class MyEventSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents()
    {
        return array(
            Events::postUpdate,
            Events::prePersist,
        );
    }

    public function postUpdate(PostUpdateEventArgs $args)
    {
        // ...
    }

    public function prePersist(PrePersistEventArgs $args)
    {
        // ...
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::prePersist)]
class MyEventSubscriber
{
    public function postUpdate(PostUpdateEventArgs $args)
    {
        // ...
    }

    public function prePersist(PrePersistEventArgs $args)
    {
        // ...
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
        if (!$this->reflectionProvider->hasClass(DoctrineClass::AS_DOCTRINE_LISTENER_ATTRIBUTE)) {
            return null;
        }
        if (!$this->hasImplements($node, DoctrineClass::EVENT_SUBSCRIBER) && !$this->hasImplements($node, DoctrineClass::EVENT_SUBSCRIBER_INTERFACE)) {
            return null;
        }
        foreach ($node->stmts as $key => $classStmt) {
            if (!$classStmt instanceof ClassMethod) {
                continue;
            }
            if (!$this->isName($classStmt, 'getSubscribedEvents')) {
                continue;
            }
            $getSubscribedEventsClassMethod = $classStmt;
            if ($getSubscribedEventsClassMethod->stmts === []) {
                continue;
            }
            //            $firstStmt = $getSubscribedEventsClassMethod->stmts[0];
            //            if ($firstStmt instanceof Return_ && $firstStmt->expr instanceof Array_
            //            ) {
            $this->refactorSubscriberArrayToClassAttributes($node, $getSubscribedEventsClassMethod);
            //            }
            $this->removeImplements($node, [DoctrineClass::EVENT_SUBSCRIBER, DoctrineClass::EVENT_SUBSCRIBER_INTERFACE]);
            // remove method
            unset($node->stmts[$key]);
            return $node;
        }
        return null;
    }
    private function refactorSubscriberArrayToClassAttributes(Class_ $class, ClassMethod $getSubscribedEventsClassMethod): void
    {
        foreach ((array) $getSubscribedEventsClassMethod->stmts as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Array_) {
                continue;
            }
            $arguments = $this->extractArrayItemsToExprs($stmt->expr);
            $this->addClassAttribute($class, $arguments);
        }
    }
    /**
     * @return array<Expr>
     */
    private function extractArrayItemsToExprs(Array_ $array): array
    {
        $arguments = [];
        foreach ($array->items as $item) {
            $arguments[] = $item->value;
        }
        return $arguments;
    }
    /**
     * @param array<Expr> $arguments
     */
    private function addClassAttribute(Class_ $class, array $arguments): void
    {
        foreach ($arguments as $argument) {
            $class->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(DoctrineClass::AS_DOCTRINE_LISTENER_ATTRIBUTE), [new Arg($argument, \false, \false, [], new Identifier('event'))])]);
        }
    }
    private function hasImplements(Class_ $class, string $interfaceFQN): bool
    {
        foreach ($class->implements as $implement) {
            if ($this->isName($implement, $interfaceFQN)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param array<string> $interfaceFQNS
     */
    private function removeImplements(Class_ $class, array $interfaceFQNS): void
    {
        foreach ($class->implements as $key => $implement) {
            if (!$this->isNames($implement, $interfaceFQNS)) {
                continue;
            }
            unset($class->implements[$key]);
        }
    }
}
