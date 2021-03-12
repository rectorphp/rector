<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\Fixture;

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->getMockBuilder('SomeClass')
           ->disableOriginalConstructor()
           ->getMock();
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\Fixture;

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->createMock('SomeClass');
    }
}

?>
