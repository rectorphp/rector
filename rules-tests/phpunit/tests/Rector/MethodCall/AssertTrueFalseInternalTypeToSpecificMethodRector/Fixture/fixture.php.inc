<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector\Fixture;

final class MyIsTypeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(\is_string($something));
        $this->assertFalse(\is_array($something));
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector\Fixture;

final class MyIsTypeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInternalType('string', $something);
        $this->assertNotInternalType('array', $something);
    }
}

?>
