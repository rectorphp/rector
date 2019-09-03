<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentInTestToDataProviderRector\Fixture;

class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->doTestMultiple([1, 2, 3]);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentInTestToDataProviderRector\Fixture;

class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(int $variable)
    {
        $this->doTestSingle($variable);
    }
    /**
     * @return int[]
     */
    public function provideDataForTest(): iterable
    {
        yield 1;
        yield 2;
        yield 3;
    }
}

?>
