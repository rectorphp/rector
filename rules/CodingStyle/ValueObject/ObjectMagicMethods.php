<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\ValueObject;

use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
final class ObjectMagicMethods
{
    /**
     * @var string[]
     */
    public const METHOD_NAMES = ['__call', '__callStatic', MethodName::CLONE, MethodName::CONSTRUCT, '__debugInfo', MethodName::DESCTRUCT, '__get', MethodName::INVOKE, '__isset', '__serialize', '__set', MethodName::SET_STATE, '__sleep', '__toString', '__unserialize', '__unset', '__wakeup'];
}
