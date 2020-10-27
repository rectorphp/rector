<?php

namespace Rector\PHPUnit\Tests\Rector\StaticCall\GetMockRector\Fixture;

final class StaticCallTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = self::getMock('SeeSomeClass');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\StaticCall\GetMockRector\Fixture;

final class StaticCallTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = self::createMock('SeeSomeClass');
    }
}

?>
