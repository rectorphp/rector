<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Enum;

final class AssertMethod
{
    /**
     * @var string
     */
    public const ASSERT_FALSE = 'assertFalse';
    /**
     * @var string
     */
    public const ASSERT_TRUE = 'assertTrue';
    /**
     * @var string
     */
    public const ASSERT_EQUALS = 'assertEquals';
    /**
     * @var string
     */
    public const ASSERT_SAME = 'assertSame';
}
