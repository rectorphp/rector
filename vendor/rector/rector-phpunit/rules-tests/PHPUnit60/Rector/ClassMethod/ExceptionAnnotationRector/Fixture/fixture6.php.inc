<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

class Fixture6Test extends TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage SomeClass::someMethod
     */
    public function testLiteralMessage(): void
    {
        throw new \Exception('Message referencing a method, SomeClass::someMethod');
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

class Fixture6Test extends TestCase
{
    public function testLiteralMessage(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SomeClass::someMethod');
        throw new \Exception('Message referencing a method, SomeClass::someMethod');
    }
}

?>
