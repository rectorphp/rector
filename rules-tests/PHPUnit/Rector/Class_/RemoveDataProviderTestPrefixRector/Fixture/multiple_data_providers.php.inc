<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\Fixture;

class MultipleDataProviders extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider testProvideData()
     * @dataProvider testNextProvideData2()
     * @dataProvider testNextProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function testProvideData()
    {
        return ['123'];
    }

    public function testNextProvideData2()
    {
        return ['123'];
    }

    public function testNextProvideData()
    {
        return ['123'];
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\Fixture;

class MultipleDataProviders extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideData()
     * @dataProvider nextProvideData2()
     * @dataProvider nextProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function provideData()
    {
        return ['123'];
    }

    public function nextProvideData2()
    {
        return ['123'];
    }

    public function nextProvideData()
    {
        return ['123'];
    }
}

?>
