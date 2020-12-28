<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

final class MethodName
{
    /**
     * @var string
     */
    public const CONSTRUCT = '__construct';

    /**
     * @var string
     */
    public const DESCTRUCT = '__destruct';

    /**
     * @var string
     */
    public const CLONE = '__clone';

    /**
     * Mostly used in unit tests
     * @var string
     */
    public const SET_UP = 'setUp';

    /**
     * Mostly used in unit tests
     * @var string
     */
    public const TEAR_DOWN = 'tearDown';

    /**
     * @var string
     */
    public const SET_STATE = '__set_state';
}
