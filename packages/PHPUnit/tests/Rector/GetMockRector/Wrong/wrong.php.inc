<?php

namespace Rector\PHPUnit\Tests\Rector\GetMockRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = $this->getMock('SomeClass');

        $secondMock = $this->buildMock()
            ->getMock();
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\GetMockRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = $this->createMock('SomeClass');

        $secondMock = $this->buildMock()
            ->getMock();
    }
}

?>
