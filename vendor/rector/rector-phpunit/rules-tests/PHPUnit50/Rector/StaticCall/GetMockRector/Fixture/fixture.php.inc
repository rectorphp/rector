<?php

namespace Rector\PHPUnit\Tests\PHPUnit50\Rector\StaticCall\GetMockRector\Fixture;

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

namespace Rector\PHPUnit\Tests\PHPUnit50\Rector\StaticCall\GetMockRector\Fixture;

final class MyGetMockTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = $this->createMock('SomeClass');
    }
}

?>
