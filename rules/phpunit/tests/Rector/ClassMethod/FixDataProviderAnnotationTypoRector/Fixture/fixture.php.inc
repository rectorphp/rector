<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\FixDataProviderAnnotationTypoRector\Fixture;

class SomeClass extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvidor testProvideData()
     * @dataPravider testProvideData()
     * @datapravider testProvideData()
     * @datapravider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\FixDataProviderAnnotationTypoRector\Fixture;

class SomeClass extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider testProvideData()
     * @dataProvider testProvideData()
     * @dataProvider testProvideData()
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }
}

?>
