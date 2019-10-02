<?php

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

class TestClass
{
    public function foo(): void
    {
        (new TraversableClass())->someMethod('some string that should remain a string', ['test_key' => 'test value']);
    }
}
