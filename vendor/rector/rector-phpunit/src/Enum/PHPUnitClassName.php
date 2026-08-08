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
     * @api might be used in public
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
    /**
     * @var string
     */
    public const INVOCATION_MOCKER = 'PHPUnit\Framework\MockObject\Builder\InvocationMocker';
    /**
     * PHPUnit 13 moved the interface out of the Builder namespace
     * @var string
     */
    public const INVOCATION_MOCKER_INTERFACE = 'PHPUnit\Framework\MockObject\InvocationMocker';
    /**
     * @var string
     */
    public const INVOCATION_STUBBER = 'PHPUnit\Framework\MockObject\InvocationStubber';
    /**
     * @var string
     */
    public const TEST_LISTENER = 'PHPUnit\Framework\TestListener';
    /**
     * @var string
     */
    public const MOCK_OBJECT = 'PHPUnit\Framework\MockObject\MockObject';
    /**
     * @var string
     */
    public const STUB = 'PHPUnit\Framework\MockObject\Stub';
    /**
     * @var string
     */
    public const SYMFONY_TYPE_TEST_CASE = 'Symfony\Component\Form\Test\TypeTestCase';
    /**
     * @var string
     */
    public const TWIG_INTEGRATION_TEST_CASE = 'Twig\Test\IntegrationTestCase';
    /**
     * @var string[]
     */
    public const TEST_CLASSES = [self::TEST_CASE, self::TEST_CASE_LEGACY];
}
