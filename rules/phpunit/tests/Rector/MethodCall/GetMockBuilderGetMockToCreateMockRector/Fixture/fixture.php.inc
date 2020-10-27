<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\Fixture;

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->getMockBuilder('SeeSomeClass')
           ->disableOriginalConstructor()
           ->getMock();
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\Fixture;

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->createMock('SeeSomeClass');
    }
}

?>
