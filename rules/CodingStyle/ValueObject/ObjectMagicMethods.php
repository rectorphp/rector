<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ValueObject;

use Rector\Core\ValueObject\MethodName;
final class ObjectMagicMethods
{
    /**
     * @var string[]
     */
    public const METHOD_NAMES = ['__call', '__callStatic', \Rector\Core\ValueObject\MethodName::CLONE, \Rector\Core\ValueObject\MethodName::CONSTRUCT, '__debugInfo', \Rector\Core\ValueObject\MethodName::DESCTRUCT, '__get', '__invoke', '__isset', '__serialize', '__set', \Rector\Core\ValueObject\MethodName::SET_STATE, '__sleep', '__toString', '__unserialize', '__unset', '__wakeup'];
}
