<?php

declare (strict_types=1);
namespace Rector\Doctrine\Orm214\Rector\Param;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\Enum\EventClass;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/orm/pull/10086
 * @see \Rector\Doctrine\Tests\Orm214\Rector\Param\ReplaceLifecycleEventArgsByDedicatedEventArgsRector\ReplaceLifecycleEventArgsByDedicatedEventArgsRectorTest
 */
final class ReplaceLifecycleEventArgsByDedicatedEventArgsRector extends AbstractRector
{
    /**
     * @var array<string, EventClass::*>
     */
    private const EVENT_CLASSES = ['prePersist' => EventClass::PRE_PERSIST_EVENT_ARGS, 'preUpdate' => EventClass::PRE_UPDATE_EVENT_ARGS, 'preRemove' => EventClass::PRE_REMOVE_EVENT_ARGS, 'postPersist' => EventClass::POST_PERSIST_EVENT_ARGS, 'postUpdate' => EventClass::POST_UPDATE_EVENT_ARGS, 'postRemove' => EventClass::POST_REMOVE_EVENT_ARGS, 'postLoad' => EventClass::POST_LOAD_EVENT_ARGS];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace Doctrine\ORM\Event\LifecycleEventArgs with specific event classes based on the function call', [new CodeSample(<<<'CODE_SAMPLE'
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
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->params === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->params as $param) {
            if (!$this->isObjectType($param, new ObjectType(DoctrineClass::LIFECYCLE_EVENT_ARGS))) {
                continue;
            }
            foreach (EventClass::ALL as $eventClass) {
                if ($this->isObjectType($param, new ObjectType($eventClass))) {
                    continue 2;
                }
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
