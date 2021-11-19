<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

class SomeClass
{
    public static function foo(stdClass $obj = new stdClass)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\Fixture;

class SomeClass
{
    public static function foo(stdClass $obj = null)
    {
        $obj ??= new stdClass;
    }
}

?>
