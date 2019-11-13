<?php declare (strict_types=1);

namespace Rector\Issue2307;

final class MyClass
{
    public function throw(): void
    {
        throw new Exception;
    }
}
