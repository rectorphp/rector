<?php declare(strict_types=1);

final class MyTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertRegExp('/^Message for ".*"\.$/', $string, $message);
        $this->assertNotRegExp('/^Message for ".*"\.$/', $string, $message);
        $this->assertNotRegExp('/^Message for ".*"\.$/', $string, $message);
        $this->assertRegExp('/^Message for ".*"\.$/', $string, $message);
    }
}
