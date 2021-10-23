<?php

declare(strict_types=1);

namespace Whatever\Foo\Bar;

use MyCLabs\Enum\Enum;

/**
 * @psalm-immutable
 */
final class WorkingEnum extends Enum
{
    public const FOO_WITH_UNDERSCORE = 'foo';
    public const BAR = 'bar';
    public const BAZ = 'baz';
    public const QOO = 'qoo';

    /**
     * @psalm-assert-if-true WorkingEnum::* $value
     * @psalm-pure
     */
    public static function isValid($value): bool
    {
        return parent::isValid($value);
    }
}
