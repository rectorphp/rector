<?php

namespace Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use PHPUnit\Framework\TestCase;

class ToSelfPHPUnitAssertTest extends TestCase
{
    public function test()
    {
        $this->assertEquals('a', 'a');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Fixture;

use PHPUnit\Framework\TestCase;

class ToSelfPHPUnitAssertTest extends TestCase
{
    public function test()
    {
        self::assertEquals('a', 'a');
    }
}

?>
