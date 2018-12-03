<?php

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertInstanceOfComparisonRector\Wrong;

use Foo\Bar;

final class MyTest extends \PHPUnit\Framework\TestCase
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

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertInstanceOfComparisonRector\Wrong;

use Foo\Bar;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertInstanceOf(Foo::class, $something);
        $this->assertNotInstanceOf(\Namespaced\Foo::class, $something);
        $this->assertInstanceOf(Bar\Baz::class, $something);
    }
}

?>
