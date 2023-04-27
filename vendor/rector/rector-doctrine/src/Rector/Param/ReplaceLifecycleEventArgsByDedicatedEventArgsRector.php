<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Param;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/orm/pull/10086
 * @see \Rector\Doctrine\Tests\Rector\Param\ReplaceLifecycleEventArgsByDedicatedEventArgsRector\ReplaceLifecycleEventArgsByDedicatedEventArgsRectorTest
 */
final class ReplaceLifecycleEventArgsByDedicatedEventArgsRector extends AbstractRector
{
    private const EVENT_CLASSES = ['prePersist' => 'Doctrine\\ORM\\Event\\PrePersistEventArgs', 'preUpdate' => 'Doctrine\\ORM\\Event\\PreUpdateEventArgs', 'preRemove' => 'Doctrine\\ORM\\Event\\PreRemoveEventArgs', 'postPersist' => 'Doctrine\\ORM\\Event\\PostPersistEventArgs', 'postUpdate' => 'Doctrine\\ORM\\Event\\PostUpdateEventArgs', 'postRemove' => 'Doctrine\\ORM\\Event\\PostRemoveEventArgs', 'postLoad' => 'Doctrine\\ORM\\Event\\PostLoadEventArgs'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace Doctrine\\ORM\\Event\\LifecycleEventArgs with specific event classes based on the function call', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Event\LifecycleEventArgs;

class PrePersistExample
{
    public function prePersist(LifecycleEventArgs $args)
    {
        // ...
    }
}

CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Event\PrePersistEventArgs;

class PrePersistExample
{
    public function prePersist(PrePersistEventArgs $args)
    {
        // ...
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node\Param>>
     */
    public function getNodeTypes() : array
    {
        return [Node\Param::class];
    }
    /**
     * @param Node\Param $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Doctrine\\ORM\\Event\\LifecycleEventArgs'))) {
            return null;
        }
        if ($this->isObjectType($node, new ObjectType('Doctrine\\ORM\\Event\\PrePersistEventArgs')) || $this->isObjectType($node, new ObjectType('Doctrine\\ORM\\Event\\PreUpdateEventArgs')) || $this->isObjectType($node, new ObjectType('Doctrine\\ORM\\Event\\PreRemoveEventArgs')) || $this->isObjectType($node, new ObjectType('Doctrine\\ORM\\Event\\PostPersistEventArgs')) || $this->isObjectType($node, new ObjectType('Doctrine\\ORM\\Event\\PostUpdateEventArgs')) || $this->isObjectType($node, new ObjectType('Doctrine\\ORM\\Event\\PostRemoveEventArgs')) || $this->isObjectType($node, new ObjectType('Doctrine\\ORM\\Event\\PostLoadEventArgs'))) {
            return null;
        }
        $classMethod = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        $eventClass = self::EVENT_CLASSES[$classMethod->name->name] ?? null;
        if ($eventClass === null) {
            return null;
        }
        $node->type = new FullyQualified($eventClass);
        return $node;
    }
}
