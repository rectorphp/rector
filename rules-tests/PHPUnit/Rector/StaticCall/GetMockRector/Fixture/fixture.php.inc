<?php

namespace Rector\Tests\PHPUnit\Rector\StaticCall\GetMockRector\Fixture;

final class MyGetMockTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = $this->getMock('SomeClass');
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\StaticCall\GetMockRector\Fixture;

final class MyGetMockTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = $this->createMock('SomeClass');
    }
}

?>
