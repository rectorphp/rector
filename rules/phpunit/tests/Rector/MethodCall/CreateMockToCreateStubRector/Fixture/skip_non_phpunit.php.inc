<?php

namespace Rector\phpunit\Tests\Rector\MethodCall\CreateMockToCreateStubRector\Fixture;

class SkipNonPHPUnit
{
    public function run()
    {
        $stub = $this->createMock(\Exception::class);
    }
}
