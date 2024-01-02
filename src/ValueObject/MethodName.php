<?php

declare (strict_types=1);
namespace Rector\ValueObject;

/**
 * @api
 * @enum
 */
final class MethodName
{
    /**
     * @var string
     */
    public const __SET = '__set';
    /**
     * @var string
     */
    public const __GET = '__get';
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
     * @see https://phpunit.readthedocs.io/en/9.3/fixtures.html#more-setup-than-teardown
     * @var string
     */
    public const SET_UP = 'setUp';
    /**
     * @var string
     */
    public const SET_STATE = '__set_state';
    /**
     * @see https://phpunit.readthedocs.io/en/9.3/fixtures.html#fixtures-sharing-fixture-examples-databasetest-php
     * @var string
     */
    public const SET_UP_BEFORE_CLASS = 'setUpBeforeClass';
    /**
     * @var string
     */
    public const CALL = '__call';
    /**
     * @var string
     */
    public const TO_STRING = '__toString';
    /**
     * @var string
     */
    public const INVOKE = '__invoke';
}
