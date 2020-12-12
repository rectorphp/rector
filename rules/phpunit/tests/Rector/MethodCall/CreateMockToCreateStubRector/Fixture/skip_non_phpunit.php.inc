<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\CreateMockToCreateStubRector\Fixture;

class SkipNonPHPUnit
{
    public function run()
    {
        $stub = $this->createMock(\Exception::class);
    }
}
