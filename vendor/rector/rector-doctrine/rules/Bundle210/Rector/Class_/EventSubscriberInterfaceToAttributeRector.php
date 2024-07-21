<?php

declare (strict_types=1);
namespace Rector\Doctrine\Bundle210\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/DoctrineBundle/pull/1664
 *
 * @see \Rector\Doctrine\Tests\Bundle210\Rector\Class_\EventSubscriberInterfaceToAttributeRector\EventSubscriberInterfaceToAttributeRectorTest
 */
final class EventSubscriberInterfaceToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var \PhpParser\Node\Stmt\Class_
     */
    private $subscriberClass;
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace EventSubscriberInterface with AsDoctrineListener attribute(s)', [new CodeSample(<<<'CODE_SAMPLE'
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
        if (!$this->hasImplements($node, 'Doctrine\\Common\\EventSubscriber') && !$this->hasImplements($node, 'Doctrine\\Bundle\\DoctrineBundle\\EventSubscriber\\EventSubscriberInterface')) {
            return null;
        }
        $this->subscriberClass = $node;
        $getSubscribedEventsClassMethod = $node->getMethod('getSubscribedEvents');
        if (!$getSubscribedEventsClassMethod instanceof ClassMethod) {
            return null;
        }
        $stmts = (array) $getSubscribedEventsClassMethod->stmts;
        if ($stmts === []) {
            return null;
        }
        if ($stmts[0] instanceof Return_ && $stmts[0]->expr instanceof Array_) {
            $this->handleArray($stmts);
        }
        $this->removeImplements($node, ['Doctrine\\Common\\EventSubscriber', 'Doctrine\\Bundle\\DoctrineBundle\\EventSubscriber\\EventSubscriberInterface']);
        unset($node->stmts[$getSubscribedEventsClassMethod->getAttribute(AttributeKey::STMT_KEY)]);
        return $node;
    }
    /**
     * @param array<int, Node\Stmt> $expressions
     */
    private function handleArray(array $expressions) : void
    {
        foreach ($expressions as $expression) {
            if (!$expression instanceof Return_ || !$expression->expr instanceof Array_) {
                continue;
            }
            $arguments = $this->parseArguments($expression->expr);
            $this->addAttribute($arguments);
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
    private function addAttribute(array $arguments) : void
    {
        foreach ($arguments as $argument) {
            $this->subscriberClass->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified('Doctrine\\Bundle\\DoctrineBundle\\Attribute\\AsDoctrineListener'), [new Arg($argument, \false, \false, [], new Identifier('event'))])]);
        }
    }
    private function hasImplements(Class_ $class, string $interfaceFQN) : bool
    {
        foreach ($class->implements as $implement) {
            if ($this->nodeNameResolver->isName($implement, $interfaceFQN)) {
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
            if (!$this->nodeNameResolver->isNames($implement, $interfaceFQNS)) {
                continue;
            }
            unset($class->implements[$key]);
        }
    }
}
