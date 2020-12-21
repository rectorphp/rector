<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\CreateMockToCreateStubRector\Fixture;

class SkipNonPhpunit
{
    public function run()
    {
        $stub = $this->createMock(\Exception::class);
    }
}
