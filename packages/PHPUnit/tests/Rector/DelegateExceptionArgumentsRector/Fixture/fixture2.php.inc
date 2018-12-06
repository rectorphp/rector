<?php

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->setExpectedExceptionRegExp('SomeException', 'SomREGEPX');
    }
}

?>
-----
<?php

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->setExpectedExceptionRegExp('SomeException');
        $this->expectExceptionMessageRegExp('SomREGEPX');
    }
}

?>
