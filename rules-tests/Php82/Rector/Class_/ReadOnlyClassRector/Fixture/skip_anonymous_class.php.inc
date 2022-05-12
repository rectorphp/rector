<?php

namespace Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\Fixture;

class SkipAnonymousClass
{
    public function run()
    {
        new class {
            private readonly string $foo;
        };
    }
}
