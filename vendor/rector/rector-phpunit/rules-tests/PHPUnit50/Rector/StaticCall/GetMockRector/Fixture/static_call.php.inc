<?php

namespace Rector\PHPUnit\Tests\PHPUnit50\Rector\StaticCall\GetMockRector\Fixture;

final class StaticCallTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = self::getMock('SomeClass');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\PHPUnit50\Rector\StaticCall\GetMockRector\Fixture;

final class StaticCallTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $firstMock = self::createMock('SomeClass');
    }
}

?>
