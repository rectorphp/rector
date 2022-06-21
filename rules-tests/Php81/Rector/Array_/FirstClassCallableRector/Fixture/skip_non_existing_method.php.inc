<?php

namespace Rector\Tests\Php81\Rector\Array_\FirstClassCallableRector\Fixture;

final class SkipNonExistingMethod
{
    public function run()
    {
        $name = [$this, 'another'];
    }

    public function name()
    {
    }
}
