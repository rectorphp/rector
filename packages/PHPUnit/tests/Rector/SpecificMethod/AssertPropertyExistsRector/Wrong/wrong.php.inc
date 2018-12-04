<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertPropertyExistsRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue(property_exists(new \stdClass(), 'property'));
        $this->assertFalse(property_exists(new \Namespaced\stdClass, 'property'), 'message');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertPropertyExistsRector\Wrong;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertClassHasAttribute('property', 'stdClass');
        $this->assertClassNotHasAttribute('property', 'Namespaced\stdClass', 'message');
    }
}

?>
