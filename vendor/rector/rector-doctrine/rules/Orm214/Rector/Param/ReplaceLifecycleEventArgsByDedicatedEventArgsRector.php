<?php

declare (strict_types=1);
namespace Rector\Doctrine\Orm214\Rector\Param;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/orm/pull/10086
 * @see \Rector\Doctrine\Tests\Orm214\Rector\Param\ReplaceLifecycleEventArgsByDedicatedEventArgsRector\ReplaceLifecycleEventArgsByDedicatedEventArgsRectorTest
 */
final class ReplaceLifecycleEventArgsByDedicatedEventArgsRector extends AbstractRector
{
    /**
     * @var array<string, class-string>
     */
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
     * @return array<class-string<ClassMethod>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->params === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->params as $param) {
            if (!$this->isObjectType($param, new ObjectType('Doctrine\\ORM\\Event\\LifecycleEventArgs'))) {
                continue;
            }
            if ($this->isObjectType($param, new ObjectType('Doctrine\\ORM\\Event\\PrePersistEventArgs')) || $this->isObjectType($param, new ObjectType('Doctrine\\ORM\\Event\\PreUpdateEventArgs')) || $this->isObjectType($param, new ObjectType('Doctrine\\ORM\\Event\\PreRemoveEventArgs')) || $this->isObjectType($param, new ObjectType('Doctrine\\ORM\\Event\\PostPersistEventArgs')) || $this->isObjectType($param, new ObjectType('Doctrine\\ORM\\Event\\PostUpdateEventArgs')) || $this->isObjectType($param, new ObjectType('Doctrine\\ORM\\Event\\PostRemoveEventArgs')) || $this->isObjectType($param, new ObjectType('Doctrine\\ORM\\Event\\PostLoadEventArgs'))) {
                continue;
            }
            $eventClass = self::EVENT_CLASSES[$node->name->name] ?? null;
            if ($eventClass === null) {
                continue;
            }
            $param->type = new FullyQualified($eventClass);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
