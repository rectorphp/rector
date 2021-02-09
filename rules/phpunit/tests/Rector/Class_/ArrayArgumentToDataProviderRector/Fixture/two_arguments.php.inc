<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentToDataProviderRector\Fixture;

class TwoArgumentsTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->doTestMultiple([['before', 'after']]);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentToDataProviderRector\Fixture;

class TwoArgumentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $variable, string $variable2)
    {
        $this->doTestSingle($variable, $variable2);
    }
    public function provideDataForTest(): \Iterator
    {
        yield [['before', 'after']];
    }
}

?>
