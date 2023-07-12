<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\MethodCall\DelegateExceptionArgumentsRector\Fixture;

final class MessageWithCodeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        self::setExpectedException('ThirdException', $message, 101);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\MethodCall\DelegateExceptionArgumentsRector\Fixture;

final class MessageWithCodeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        self::expectException('ThirdException');
        self::expectExceptionMessage($message);
        self::expectExceptionCode(101);
    }
}

?>
