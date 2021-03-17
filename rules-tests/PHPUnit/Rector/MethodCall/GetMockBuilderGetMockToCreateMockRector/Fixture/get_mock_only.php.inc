<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\Fixture;

class GetMockOnly extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->getMockBuilder('SomeClass')
            ->getMock();
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector\Fixture;

class GetMockOnly extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $applicationMock = $this->createMock('SomeClass');
    }
}

?>
