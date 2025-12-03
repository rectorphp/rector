<?php

declare (strict_types=1);
namespace Rector\Doctrine\Enum;

final class EventClass
{
    public const PRE_PERSIST_EVENT_ARGS = 'Doctrine\ORM\Event\PrePersistEventArgs';
    public const PRE_UPDATE_EVENT_ARGS = 'Doctrine\ORM\Event\PreUpdateEventArgs';
    public const PRE_REMOVE_EVENT_ARGS = 'Doctrine\ORM\Event\PreRemoveEventArgs';
    public const POST_PERSIST_EVENT_ARGS = 'Doctrine\ORM\Event\PostPersistEventArgs';
    public const POST_UPDATE_EVENT_ARGS = 'Doctrine\ORM\Event\PostUpdateEventArgs';
    public const POST_REMOVE_EVENT_ARGS = 'Doctrine\ORM\Event\PostRemoveEventArgs';
    public const POST_LOAD_EVENT_ARGS = 'Doctrine\ORM\Event\PostLoadEventArgs';
    /**
     * @var array<class-string>
     */
    public const ALL = [self::PRE_PERSIST_EVENT_ARGS, self::PRE_UPDATE_EVENT_ARGS, self::PRE_REMOVE_EVENT_ARGS, self::POST_PERSIST_EVENT_ARGS, self::POST_UPDATE_EVENT_ARGS, self::POST_REMOVE_EVENT_ARGS, self::POST_LOAD_EVENT_ARGS];
}
