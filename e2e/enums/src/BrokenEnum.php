<?php

declare(strict_types=1);

namespace Whatever\Foo\Bar;

use MyCLabs\Enum\Enum;

/**
 * @template-extends Enum<BrokenEnum::*>
 * @psalm-immutable
 */
final class BrokenEnum extends Enum
{
    public const FOO_WITH_UNDERSCORE = 'foo';
    public const BAR = 'bar';
    public const BAZ = 'baz';
    public const QOO = 'qoo';
}
