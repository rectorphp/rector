<?php

declare(strict_types=1);

namespace Rector\Parallel\ValueObject;

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
