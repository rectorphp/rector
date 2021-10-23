<?php
declare(strict_types=1);

namespace Whatever\Foo\Bar;

final class FileConsumingBrokenEnum
{
    public const FOO = BrokenEnum::FOO_WITH_UNDERSCORE;
}
