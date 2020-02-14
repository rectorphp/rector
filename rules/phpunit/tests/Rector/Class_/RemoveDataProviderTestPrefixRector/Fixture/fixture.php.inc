<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\Fixture;

class SomeClass extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function testProvideData()
    {
        return ['123'];
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\Fixture;

class SomeClass extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function provideData()
    {
        return ['123'];
    }
}

?>
