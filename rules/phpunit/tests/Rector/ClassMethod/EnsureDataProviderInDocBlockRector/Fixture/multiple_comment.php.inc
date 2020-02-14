<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\EnsureDataProviderInDocBlockRector\Fixture;

use PHPUnit\Framework\TestCase;

class MultipleComment extends TestCase
{
    /*
     * This is just a comment
     *
     * @dataProvider testProvideData()
     *
     * in between
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

class MultipleComment extends TestCase
{
    /**
     * This is just a comment
     *
     * @dataProvider testProvideData()
     *
     * in between
     */
    public function test()
    {
        $nothing = 5;
    }
}

?>
