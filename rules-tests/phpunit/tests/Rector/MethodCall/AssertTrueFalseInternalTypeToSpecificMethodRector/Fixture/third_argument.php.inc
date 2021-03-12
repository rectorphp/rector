<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector\Fixture;

final class ThridArgument extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(is_integer($something), 'third argument');
        $this->assertFalse(is_bool($something), 'third argument');

        $this->assertTrue(is_file($something));
        $this->assertFalse(is_null($something), 'second argument');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector\Fixture;

final class ThridArgument extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInternalType('integer', $something, 'third argument');
        $this->assertNotInternalType('bool', $something, 'third argument');

        $this->assertTrue(is_file($something));
        $this->assertFalse(is_null($something), 'second argument');
    }
}

?>
