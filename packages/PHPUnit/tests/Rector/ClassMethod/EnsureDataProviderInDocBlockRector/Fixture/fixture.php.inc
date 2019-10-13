<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\EnsureDataProviderInDocBlockRector\Fixture;

use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    /*
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\EnsureDataProviderInDocBlockRector\Fixture;

use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    /**
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }
}

?>
