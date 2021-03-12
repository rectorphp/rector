<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\CreateMockToCreateStubRector\Fixture;

class SkipNonPhpunit
{
    public function run()
    {
        $stub = $this->createMock(\Exception::class);
    }
}
