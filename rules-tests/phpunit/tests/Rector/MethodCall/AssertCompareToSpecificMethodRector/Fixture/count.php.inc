<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertCompareToSpecificMethodRector\Fixture;

final class Count extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertSame(5, count($something));
        $this->assertEquals(10, iterator_count($something));

        $count = 92;
        $this->assertNotEquals($count, sizeof($something), 'third argument');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertCompareToSpecificMethodRector\Fixture;

final class Count extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertCount(5, $something);
        $this->assertCount(10, $something);

        $count = 92;
        $this->assertNotCount($count, $something, 'third argument');
    }
}

?>
