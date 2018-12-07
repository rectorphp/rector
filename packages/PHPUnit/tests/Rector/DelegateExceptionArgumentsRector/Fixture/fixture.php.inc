<?php

namespace Rector\PHPUnit\Tests\Rector\DelegateExceptionArgumentsRector\Fixture;

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->setExpectedException('SomeException');
        $this->setExpectedException('SomeException', $message);
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
        $this->setExpectedException('SomeException');
        $this->setExpectedException('SomeException');
        $this->expectExceptionMessage($message);
    }
}

?>
