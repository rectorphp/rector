<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Enum;

final class ConsecutiveMethodName
{
    /**
     * @var string
     */
    public const WILL_RETURN_ON_CONSECUTIVE_CALLS = 'willReturnOnConsecutiveCalls';
    /**
     * @var string
     */
    public const WILL_RETURN_ARGUMENT = 'willReturnArgument';
    /**
     * @var string
     */
    public const WILL_RETURN_SELF = 'willReturnSelf';
    /**
     * @var string
     */
    public const WILL_THROW_EXCEPTION = 'willThrowException';
    /**
     * @var string
     */
    public const WILL_RETURN_REFERENCE = 'willReturnReference';
    /**
     * @var string
     */
    public const WILL_RETURN = 'willReturn';
    /**
     * @var string
     */
    public const WILL_RETURN_CALLBACK = 'willReturnCallback';
    /**
     * @var string
     */
    public const WITH_CONSECUTIVE = 'withConsecutive';
}
