<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class SkipNotIdentifierName
{
    public function run(string $name)
    {
        return $this->{'do' . $name}();
    }

    private function doFoo()
    {
        return 5;
    }

    private function doBar()
    {
        return 4;
    }
}