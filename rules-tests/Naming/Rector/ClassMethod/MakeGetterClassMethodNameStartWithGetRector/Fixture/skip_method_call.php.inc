<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector\Fixture;

class SkipMethodCall
{
    public function name(): string
    {
        return $this->that();
    }

    public function that()
    {
        return 1000;
    }
}
