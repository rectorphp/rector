<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertTrueFalseInternalTypeToSpecificMethodRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(\is_string($something));
        $this->assertFalse(\is_array($something));

        $this->assertTrue(is_integer($something), 'third argument');
        $this->assertFalse(is_bool($something), 'third argument');

        $this->assertTrue(is_file($something));
        $this->assertFalse(is_null($something), 'second argument');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertTrueFalseInternalTypeToSpecificMethodRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInternalType('string', $something);
        $this->assertNotInternalType('array', $something);

        $this->assertInternalType('integer', $something, 'third argument');
        $this->assertNotInternalType('bool', $something, 'third argument');

        $this->assertTrue(is_file($something));
        $this->assertFalse(is_null($something), 'second argument');
    }
}

?>
