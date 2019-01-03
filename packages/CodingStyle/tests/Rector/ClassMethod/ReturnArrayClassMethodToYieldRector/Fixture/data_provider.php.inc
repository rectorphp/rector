<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;

class DataProvider extends ParentTestCase
{
    public static function provideData()
    {
        return [
            ['<?php implode("", $foo, );', '<?php implode($foo, "", );']
        ];
    }

    public static function dataProvider()
    {
        return [
            ['<?php implode(\'\', $foo, );', '<?php implode($foo, );'],
            ['<?php implode(\'\', $foo, );', '<?php implode($foo, );']
        ];
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;

class DataProvider extends ParentTestCase
{
    public static function provideData()
    {
        yield ['<?php implode("", $foo, );', '<?php implode($foo, "", );'];
    }

    public static function dataProvider()
    {
        yield ['<?php implode(\'\', $foo, );', '<?php implode($foo, );'];
        yield ['<?php implode(\'\', $foo, );', '<?php implode($foo, );'];
    }
}

?>
