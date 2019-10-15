<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\Fixture;

class WithTestAnnotation extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @dataProvider testProvideDataForWithATestAnnotation()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function testProvideDataForWithATestAnnotation()
    {
        return ['123'];
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\Fixture;

class WithTestAnnotation extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @dataProvider provideDataForWithATestAnnotation()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function provideDataForWithATestAnnotation()
    {
        return ['123'];
    }
}

?>
