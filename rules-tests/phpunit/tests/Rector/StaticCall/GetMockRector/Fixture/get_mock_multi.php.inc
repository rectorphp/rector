<?php

namespace Rector\PHPUnit\Tests\Rector\StaticCall\GetMockRector\Fixture;

final class GetMockMultiTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = $this->getMock('LDUser', [], [], '', false);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\StaticCall\GetMockRector\Fixture;

final class GetMockMultiTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = $this->createMock('LDUser');
    }
}

?>
