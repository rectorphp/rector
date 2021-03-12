<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertInstanceOfComparisonRector\Fixture;

use Foo\Bar;

final class MyOfComparissonTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertTrue($something instanceof Foo);
        $this->assertFalse($something instanceof \Namespaced\Foo);
        $this->assertTrue($something instanceof Bar\Baz);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\MethodCall\AssertInstanceOfComparisonRector\Fixture;

use Foo\Bar;

final class MyOfComparissonTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInstanceOf(\Rector\PHPUnit\Tests\Rector\MethodCall\AssertInstanceOfComparisonRector\Fixture\Foo::class, $something);
        $this->assertNotInstanceOf(\Namespaced\Foo::class, $something);
        $this->assertInstanceOf(\Foo\Bar\Baz::class, $something);
    }
}

?>
