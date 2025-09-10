<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Enum;

final class PHPUnitClassName
{
    /**
     * @var string
     */
    public const TEST_CASE = 'PHPUnit\Framework\TestCase';
    /**
     * @var string
     */
    public const TEST_CASE_LEGACY = 'PHPUnit_Framework_TestCase';
    /**
     * @var string
     */
    public const ASSERT = 'PHPUnit\Framework\Assert';
    /**
     * @var string
     */
    public const INVOCATION_ORDER = 'PHPUnit\Framework\MockObject\Rule\InvocationOrder';
}
