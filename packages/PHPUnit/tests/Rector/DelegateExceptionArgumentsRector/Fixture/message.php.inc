<?php

namespace Rector\PHPUnit\Tests\Rector\DelegateExceptionArgumentsRector\Fixture;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->setExpectedException('SomeException');
        // with message
        $this->setExpectedException('SomeException', $message);
        // with code
        self::setExpectedException('SomeException', $message, 101);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\DelegateExceptionArgumentsRector\Fixture;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectException('SomeException');
        // with message
        $this->expectException('SomeException');
        $this->expectExceptionMessage($message);
        // with code
        self::expectException('SomeException');
        self::expectExceptionMessage($message);
        self::expectExceptionCode(101);
    }
}

?>
