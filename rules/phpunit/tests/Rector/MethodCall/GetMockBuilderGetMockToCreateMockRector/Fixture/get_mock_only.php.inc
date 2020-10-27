<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\Fixture;

class GetMockOnly extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->getMockBuilder('SeeSomeClass')
            ->getMock();
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\Fixture;

class GetMockOnly extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->createMock('SeeSomeClass');
    }
}

?>
