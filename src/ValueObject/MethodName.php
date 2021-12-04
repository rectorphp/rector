<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

/**
 * @enum
 */
final class MethodName
{
    /**
     * @var string
     */
    final public const __SET = '__set';

    /**
     * @var string
     */
    final public const __GET = '__get';

    /**
     * @var string
     */
    final public const CONSTRUCT = '__construct';

    /**
     * @var string
     */
    final public const DESCTRUCT = '__destruct';

    /**
     * @var string
     */
    final public const CLONE = '__clone';

    /**
     * Mostly used in unit tests
     * @see https://phpunit.readthedocs.io/en/9.3/fixtures.html#more-setup-than-teardown
     * @var string
     */
    final public const SET_UP = 'setUp';

    /**
     * Mostly used in unit tests
     * @var string
     */
    final public const TEAR_DOWN = 'tearDown';

    /**
     * @var string
     */
    final public const SET_STATE = '__set_state';

    /**
     * @see https://phpunit.readthedocs.io/en/9.3/fixtures.html#fixtures-sharing-fixture-examples-databasetest-php
     * @var string
     */
    final public const SET_UP_BEFORE_CLASS = 'setUpBeforeClass';

    /**
     * @var string
     */
    final public const CALL = '__call';

    /**
     * @var string
     */
    final public const TO_STRING = '__toString';

    /**
     * @var string
     */
    final public const INVOKE = '__invoke';
}
