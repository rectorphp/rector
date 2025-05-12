<?php

declare (strict_types=1);
namespace Rector\Doctrine\Bundle210\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
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
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : RuleDefinition
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
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->reflectionProvider->hasClass(DoctrineClass::AS_DOCTRINE_LISTENER_ATTRIBUTE)) {
            return null;
        }
        if (!$this->hasImplements($node, DoctrineClass::EVENT_SUBSCRIBER) && !$this->hasImplements($node, DoctrineClass::EVENT_SUBSCRIBER_INTERFACE)) {
            return null;
        }
        //        $this->subscriberClass = $class;
        $getSubscribedEventsClassMethod = $node->getMethod('getSubscribedEvents');
        if (!$getSubscribedEventsClassMethod instanceof ClassMethod) {
            return null;
        }
        $stmts = (array) $getSubscribedEventsClassMethod->stmts;
        if ($stmts === []) {
            return null;
        }
        if ($stmts[0] instanceof Return_ && $stmts[0]->expr instanceof Array_) {
            $this->handleArray($node, $stmts);
        }
        $this->removeImplements($node, [DoctrineClass::EVENT_SUBSCRIBER, DoctrineClass::EVENT_SUBSCRIBER_INTERFACE]);
        unset($node->stmts[$getSubscribedEventsClassMethod->getAttribute(AttributeKey::STMT_KEY)]);
        return $node;
    }
    /**
     * @param array<int, Node\Stmt> $expressions
     */
    private function handleArray(Class_ $class, array $expressions) : void
    {
        foreach ($expressions as $expression) {
            if (!$expression instanceof Return_ || !$expression->expr instanceof Array_) {
                continue;
            }
            $arguments = $this->parseArguments($expression->expr);
            $this->addAttribute($class, $arguments);
        }
    }
    /**
     * @return array<Expr>
     */
    private function parseArguments(Array_ $array) : array
    {
        foreach ($array->items as $item) {
            if (!$item instanceof ArrayItem) {
                continue;
            }
            $arguments[] = $item->value;
        }
        return $arguments ?? [];
    }
    /**
     * @param array<Expr> $arguments
     */
    private function addAttribute(Class_ $class, array $arguments) : void
    {
        foreach ($arguments as $argument) {
            $class->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(DoctrineClass::AS_DOCTRINE_LISTENER_ATTRIBUTE), [new Arg($argument, \false, \false, [], new Identifier('event'))])]);
        }
    }
    private function hasImplements(Class_ $class, string $interfaceFQN) : bool
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
    private function removeImplements(Class_ $class, array $interfaceFQNS) : void
    {
        foreach ($class->implements as $key => $implement) {
            if (!$this->isNames($implement, $interfaceFQNS)) {
                continue;
            }
            unset($class->implements[$key]);
        }
    }
}
