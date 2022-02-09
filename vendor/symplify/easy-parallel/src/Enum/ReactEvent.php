<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Symplify\EasyParallel\Enum;

/**
 * @enum
 */
final class ReactEvent
{
    /**
     * @var string
     */
    public const EXIT = 'exit';
    /**
     * @var string
     */
    public const DATA = 'data';
    /**
     * @var string
     */
    public const ERROR = 'error';
    /**
     * @var string
     */
    public const CONNECTION = 'connection';
}
