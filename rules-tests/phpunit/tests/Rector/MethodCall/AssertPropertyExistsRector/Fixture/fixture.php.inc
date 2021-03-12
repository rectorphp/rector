<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertPropertyExistsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyAssertPropertyExistTest extends TestCase
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

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertPropertyExistsRector\Fixture;

use PHPUnit\Framework\TestCase;

final class MyAssertPropertyExistTest extends TestCase
{
    public function test()
    {
        $this->assertClassHasAttribute('property', 'stdClass');
        $this->assertClassNotHasAttribute('property', 'Namespaced\stdClass', 'message');
    }
}

?>
