<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\CreateMockToCreateStubRector\Fixture;

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function testItBehavesAsExpected(): void
    {
        $stub = $this->createMock(\Exception::class);
        $stub->method('getMessage')
            ->willReturn('a message');
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\CreateMockToCreateStubRector\Fixture;

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function testItBehavesAsExpected(): void
    {
        $stub = $this->createStub(\Exception::class);
        $stub->method('getMessage')
            ->willReturn('a message');
    }
}

?>
