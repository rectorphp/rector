<?php
declare(strict_types=1);

namespace Whatever\Foo\Bar;

final class FileConsumingBrokenEnum2
{
    public function run()
    {
        BrokenEnum::FOO_WITH_UNDERSCORE();
    }
}
